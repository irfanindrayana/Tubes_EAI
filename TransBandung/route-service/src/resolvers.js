const { getDB } = require('./db');

const resolvers = {
  Query: {
    route: async (_, { id }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM routes WHERE id = ?', [id]);
        if (rows.length === 0) return null;
        return formatRoute(rows[0]);
      } catch (error) {
        console.error('Error fetching route:', error);
        throw new Error('Failed to fetch route');
      }
    },
    
    routes: async () => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM routes');
        return rows.map(route => formatRoute(route));
      } catch (error) {
        console.error('Error fetching routes:', error);
        throw new Error('Failed to fetch routes');
      }
    },
    
    searchRoutes: async (_, { query }) => {
      try {
        const pool = getDB();
        const searchQuery = `%${query}%`;
        const [rows] = await pool.query(
          'SELECT * FROM routes WHERE name LIKE ? OR start_point LIKE ? OR end_point LIKE ?',
          [searchQuery, searchQuery, searchQuery]
        );
        return rows.map(route => formatRoute(route));
      } catch (error) {
        console.error('Error searching routes:', error);
        throw new Error('Failed to search routes');
      }
    },
    
    schedule: async (_, { id }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM schedules WHERE id = ?', [id]);
        if (rows.length === 0) return null;
        return formatSchedule(rows[0]);
      } catch (error) {
        console.error('Error fetching schedule:', error);
        throw new Error('Failed to fetch schedule');
      }
    },
    
    schedules: async (_, { routeId, dayOfWeek }) => {
      try {
        const pool = getDB();
        let query = 'SELECT * FROM schedules';
        const params = [];
        
        const conditions = [];
        if (routeId) {
          conditions.push('route_id = ?');
          params.push(routeId);
        }
        
        if (dayOfWeek) {
          conditions.push('day_of_week = ?');
          params.push(dayOfWeek);
        }
        
        if (conditions.length > 0) {
          query += ' WHERE ' + conditions.join(' AND ');
        }
        
        const [rows] = await pool.query(query, params);
        return rows.map(schedule => formatSchedule(schedule));
      } catch (error) {
        console.error('Error fetching schedules:', error);
        throw new Error('Failed to fetch schedules');
      }
    },
    
    schedulesByRoute: async (_, { routeId }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM schedules WHERE route_id = ?', [routeId]);
        return rows.map(schedule => formatSchedule(schedule));
      } catch (error) {
        console.error('Error fetching schedules by route:', error);
        throw new Error('Failed to fetch schedules by route');
      }
    },
    
    schedulesByDay: async (_, { dayOfWeek }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM schedules WHERE day_of_week = ?', [dayOfWeek]);
        return rows.map(schedule => formatSchedule(schedule));
      } catch (error) {
        console.error('Error fetching schedules by day:', error);
        throw new Error('Failed to fetch schedules by day');
      }
    }
  },
  
  Mutation: {
    createRoute: async (_, { input }) => {
      const { name, startPoint, endPoint, distance, description } = input;
      
      try {
        const pool = getDB();
        
        // Insert route
        const [result] = await pool.query(
          'INSERT INTO routes (name, start_point, end_point, distance, description) VALUES (?, ?, ?, ?, ?)',
          [name, startPoint, endPoint, distance, description]
        );
        
        const [newRoute] = await pool.query('SELECT * FROM routes WHERE id = ?', [result.insertId]);
        
        return formatRoute(newRoute[0]);
      } catch (error) {
        console.error('Error creating route:', error);
        throw new Error(`Failed to create route: ${error.message}`);
      }
    },
    
    updateRoute: async (_, { id, input }) => {
      try {
        const pool = getDB();
        
        // Check if route exists
        const [existingRoute] = await pool.query('SELECT * FROM routes WHERE id = ?', [id]);
        if (existingRoute.length === 0) {
          throw new Error('Route not found');
        }
        
        // Prepare update fields
        const updateFields = [];
        const updateValues = [];
        
        if (input.name) {
          updateFields.push('name = ?');
          updateValues.push(input.name);
        }
        
        if (input.startPoint) {
          updateFields.push('start_point = ?');
          updateValues.push(input.startPoint);
        }
        
        if (input.endPoint) {
          updateFields.push('end_point = ?');
          updateValues.push(input.endPoint);
        }
        
        if (input.distance) {
          updateFields.push('distance = ?');
          updateValues.push(input.distance);
        }
        
        if (input.description !== undefined) {
          updateFields.push('description = ?');
          updateValues.push(input.description);
        }
        
        if (updateFields.length === 0) {
          throw new Error('No fields to update');
        }
        
        // Update route
        await pool.query(
          `UPDATE routes SET ${updateFields.join(', ')} WHERE id = ?`,
          [...updateValues, id]
        );
        
        // Get updated route
        const [updatedRoute] = await pool.query('SELECT * FROM routes WHERE id = ?', [id]);
        
        return formatRoute(updatedRoute[0]);
      } catch (error) {
        console.error('Error updating route:', error);
        throw new Error(`Failed to update route: ${error.message}`);
      }
    },
    
    deleteRoute: async (_, { id }) => {
      try {
        const pool = getDB();
        
        // Check if route exists
        const [existingRoute] = await pool.query('SELECT * FROM routes WHERE id = ?', [id]);
        if (existingRoute.length === 0) {
          throw new Error('Route not found');
        }
        
        // Check for associated schedules
        const [schedules] = await pool.query('SELECT COUNT(*) as count FROM schedules WHERE route_id = ?', [id]);
        if (schedules[0].count > 0) {
          throw new Error('Cannot delete route with associated schedules');
        }
        
        // Delete route
        await pool.query('DELETE FROM routes WHERE id = ?', [id]);
        
        return true;
      } catch (error) {
        console.error('Error deleting route:', error);
        throw new Error(`Failed to delete route: ${error.message}`);
      }
    },
    
    createSchedule: async (_, { input }) => {
      const { routeId, departureTime, arrivalTime, busNumber, capacity, price, dayOfWeek } = input;
      
      try {
        const pool = getDB();
        
        // Check if route exists
        const [existingRoute] = await pool.query('SELECT * FROM routes WHERE id = ?', [routeId]);
        if (existingRoute.length === 0) {
          throw new Error('Route not found');
        }
        
        // Insert schedule
        const [result] = await pool.query(
          'INSERT INTO schedules (route_id, departure_time, arrival_time, bus_number, capacity, price, day_of_week) VALUES (?, ?, ?, ?, ?, ?, ?)',
          [routeId, departureTime, arrivalTime, busNumber, capacity, price, dayOfWeek]
        );
        
        const [newSchedule] = await pool.query('SELECT * FROM schedules WHERE id = ?', [result.insertId]);
        
        return formatSchedule(newSchedule[0]);
      } catch (error) {
        console.error('Error creating schedule:', error);
        throw new Error(`Failed to create schedule: ${error.message}`);
      }
    },
    
    updateSchedule: async (_, { id, input }) => {
      try {
        const pool = getDB();
        
        // Check if schedule exists
        const [existingSchedule] = await pool.query('SELECT * FROM schedules WHERE id = ?', [id]);
        if (existingSchedule.length === 0) {
          throw new Error('Schedule not found');
        }
        
        // Prepare update fields
        const updateFields = [];
        const updateValues = [];
        
        if (input.departureTime) {
          updateFields.push('departure_time = ?');
          updateValues.push(input.departureTime);
        }
        
        if (input.arrivalTime) {
          updateFields.push('arrival_time = ?');
          updateValues.push(input.arrivalTime);
        }
        
        if (input.busNumber) {
          updateFields.push('bus_number = ?');
          updateValues.push(input.busNumber);
        }
        
        if (input.capacity) {
          updateFields.push('capacity = ?');
          updateValues.push(input.capacity);
        }
        
        if (input.price) {
          updateFields.push('price = ?');
          updateValues.push(input.price);
        }
        
        if (input.dayOfWeek) {
          updateFields.push('day_of_week = ?');
          updateValues.push(input.dayOfWeek);
        }
        
        if (updateFields.length === 0) {
          throw new Error('No fields to update');
        }
        
        // Update schedule
        await pool.query(
          `UPDATE schedules SET ${updateFields.join(', ')} WHERE id = ?`,
          [...updateValues, id]
        );
        
        // Get updated schedule
        const [updatedSchedule] = await pool.query('SELECT * FROM schedules WHERE id = ?', [id]);
        
        return formatSchedule(updatedSchedule[0]);
      } catch (error) {
        console.error('Error updating schedule:', error);
        throw new Error(`Failed to update schedule: ${error.message}`);
      }
    },
    
    deleteSchedule: async (_, { id }) => {
      try {
        const pool = getDB();
        
        // Check if schedule exists
        const [existingSchedule] = await pool.query('SELECT * FROM schedules WHERE id = ?', [id]);
        if (existingSchedule.length === 0) {
          throw new Error('Schedule not found');
        }
        
        // Delete schedule
        await pool.query('DELETE FROM schedules WHERE id = ?', [id]);
        
        return true;
      } catch (error) {
        console.error('Error deleting schedule:', error);
        throw new Error(`Failed to delete schedule: ${error.message}`);
      }
    }
  },
  
  Route: {
    schedules: async (route) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM schedules WHERE route_id = ?', [route.id]);
        return rows.map(schedule => formatSchedule(schedule));
      } catch (error) {
        console.error('Error fetching schedules for route:', error);
        return [];
      }
    }
  },
  
  Schedule: {
    route: async (schedule) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM routes WHERE id = ?', [schedule.routeId]);
        if (rows.length === 0) return null;
        return formatRoute(rows[0]);
      } catch (error) {
        console.error('Error fetching route for schedule:', error);
        return null;
      }
    }
  }
};

// Format route object to match GraphQL schema
function formatRoute(route) {
  return {
    id: route.id,
    name: route.name,
    startPoint: route.start_point,
    endPoint: route.end_point,
    distance: route.distance,
    description: route.description,
    createdAt: route.created_at,
    updatedAt: route.updated_at,
  };
}

// Format schedule object to match GraphQL schema
function formatSchedule(schedule) {
  return {
    id: schedule.id,
    routeId: schedule.route_id,
    departureTime: schedule.departure_time,
    arrivalTime: schedule.arrival_time,
    busNumber: schedule.bus_number,
    capacity: schedule.capacity,
    price: schedule.price,
    dayOfWeek: schedule.day_of_week,
    createdAt: schedule.created_at,
    updatedAt: schedule.updated_at,
  };
}

module.exports = { resolvers };
