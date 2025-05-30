const fetch = require('node-fetch');
const { getDB } = require('./db');

// Service URLs
const USER_SERVICE_URL = process.env.USER_SERVICE_URL || 'http://localhost:4001/graphql';
const ROUTE_SERVICE_URL = process.env.ROUTE_SERVICE_URL || 'http://localhost:4003/graphql';

// Helper function to fetch data from other services
async function fetchFromService(url, query, variables = {}) {
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        query,
        variables,
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      console.error('GraphQL Error:', result.errors);
      throw new Error(result.errors[0].message);
    }
    
    return result.data;
  } catch (error) {
    console.error(`Error fetching from service at ${url}:`, error);
    throw error;
  }
}

const resolvers = {
  Query: {
    booking: async (_, { id }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM bookings WHERE id = ?', [id]);
        if (rows.length === 0) return null;
        return formatBooking(rows[0]);
      } catch (error) {
        console.error('Error fetching booking:', error);
        throw new Error('Failed to fetch booking');
      }
    },
    
    bookings: async () => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM bookings');
        return rows.map(booking => formatBooking(booking));
      } catch (error) {
        console.error('Error fetching bookings:', error);
        throw new Error('Failed to fetch bookings');
      }
    },
    
    bookingsByUser: async (_, { userId }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM bookings WHERE user_id = ?', [userId]);
        return rows.map(booking => formatBooking(booking));
      } catch (error) {
        console.error('Error fetching bookings by user:', error);
        throw new Error('Failed to fetch bookings by user');
      }
    },
    
    bookingsBySchedule: async (_, { scheduleId }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query(
          'SELECT * FROM bookings WHERE schedule_id = ?',
          [scheduleId]
        );
        return rows.map(booking => formatBooking(booking));
      } catch (error) {
        console.error('Error fetching bookings by schedule:', error);
        throw new Error('Failed to fetch bookings by schedule');
      }
    },
    
    isSeatAvailable: async (_, { scheduleId, bookingDate, seatNumber }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query(
          'SELECT COUNT(*) as count FROM bookings WHERE schedule_id = ? AND booking_date = ? AND seat_number = ? AND status != "cancelled"',
          [scheduleId, bookingDate, seatNumber]
        );
        return rows[0].count === 0;
      } catch (error) {
        console.error('Error checking seat availability:', error);
        throw new Error('Failed to check seat availability');
      }
    },
    
    availableSeats: async (_, { scheduleId, bookingDate }) => {
      try {
        const pool = getDB();
        
        // Get total capacity from route service
        const scheduleQuery = `
          query GetSchedule($id: ID!) {
            schedule(id: $id) {
              capacity
            }
          }
        `;
        
        const scheduleResult = await fetchFromService(ROUTE_SERVICE_URL, scheduleQuery, { id: scheduleId });
        const capacity = scheduleResult.schedule.capacity;
        
        // Get booked seats
        const [rows] = await pool.query(
          'SELECT seat_number FROM bookings WHERE schedule_id = ? AND booking_date = ? AND status != "cancelled"',
          [scheduleId, bookingDate]
        );
        
        const bookedSeats = rows.map(row => row.seat_number);
        const allSeats = Array.from({ length: capacity }, (_, i) => i + 1);
        
        return allSeats.filter(seat => !bookedSeats.includes(seat));
      } catch (error) {
        console.error('Error fetching available seats:', error);
        throw new Error('Failed to fetch available seats');
      }
    }
  },
  
  Mutation: {
    createBooking: async (_, { input }) => {
      const { userId, scheduleId, bookingDate, seatNumber } = input;
      
      try {
        const pool = getDB();
        
        // Check if seat is available
        const [existingBookings] = await pool.query(
          'SELECT * FROM bookings WHERE schedule_id = ? AND booking_date = ? AND seat_number = ? AND status != "cancelled"',
          [scheduleId, bookingDate, seatNumber]
        );
        
        if (existingBookings.length > 0) {
          throw new Error('Seat is already booked');
        }
        
        // Create booking
        const [result] = await pool.query(
          'INSERT INTO bookings (user_id, schedule_id, booking_date, seat_number, status) VALUES (?, ?, ?, ?, "pending")',
          [userId, scheduleId, bookingDate, seatNumber]
        );
        
        const [newBooking] = await pool.query('SELECT * FROM bookings WHERE id = ?', [result.insertId]);
        
        return formatBooking(newBooking[0]);
      } catch (error) {
        console.error('Error creating booking:', error);
        throw new Error(`Failed to create booking: ${error.message}`);
      }
    },
    
    updateBooking: async (_, { id, input }) => {
      try {
        const pool = getDB();
        
        // Check if booking exists
        const [existingBooking] = await pool.query('SELECT * FROM bookings WHERE id = ?', [id]);
        if (existingBooking.length === 0) {
          throw new Error('Booking not found');
        }
        
        // Prepare update fields
        const updateFields = [];
        const updateValues = [];
        
        if (input.status) {
          updateFields.push('status = ?');
          updateValues.push(input.status);
        }
        
        if (input.bookingDate) {
          updateFields.push('booking_date = ?');
          updateValues.push(input.bookingDate);
        }
        
        if (input.seatNumber) {
          // Check if seat is available
          const [existingBookings] = await pool.query(
            'SELECT * FROM bookings WHERE schedule_id = ? AND booking_date = ? AND seat_number = ? AND status != "cancelled" AND id != ?',
            [existingBooking[0].schedule_id, input.bookingDate || existingBooking[0].booking_date, input.seatNumber, id]
          );
          
          if (existingBookings.length > 0) {
            throw new Error('Seat is already booked');
          }
          
          updateFields.push('seat_number = ?');
          updateValues.push(input.seatNumber);
        }
        
        if (updateFields.length === 0) {
          throw new Error('No fields to update');
        }
        
        // Update booking
        await pool.query(
          `UPDATE bookings SET ${updateFields.join(', ')} WHERE id = ?`,
          [...updateValues, id]
        );
        
        // Get updated booking
        const [updatedBooking] = await pool.query('SELECT * FROM bookings WHERE id = ?', [id]);
        
        return formatBooking(updatedBooking[0]);
      } catch (error) {
        console.error('Error updating booking:', error);
        throw new Error(`Failed to update booking: ${error.message}`);
      }
    },
    
    cancelBooking: async (_, { id }) => {
      try {
        const pool = getDB();
        
        // Check if booking exists
        const [existingBooking] = await pool.query('SELECT * FROM bookings WHERE id = ?', [id]);
        if (existingBooking.length === 0) {
          throw new Error('Booking not found');
        }
        
        // Update booking status to cancelled
        await pool.query('UPDATE bookings SET status = "cancelled" WHERE id = ?', [id]);
        
        // Get updated booking
        const [updatedBooking] = await pool.query('SELECT * FROM bookings WHERE id = ?', [id]);
        
        return formatBooking(updatedBooking[0]);
      } catch (error) {
        console.error('Error cancelling booking:', error);
        throw new Error(`Failed to cancel booking: ${error.message}`);
      }
    },
    
    completeBooking: async (_, { id }) => {
      try {
        const pool = getDB();
        
        // Check if booking exists
        const [existingBooking] = await pool.query('SELECT * FROM bookings WHERE id = ?', [id]);
        if (existingBooking.length === 0) {
          throw new Error('Booking not found');
        }
        
        // Update booking status to completed
        await pool.query('UPDATE bookings SET status = "completed" WHERE id = ?', [id]);
        
        // Get updated booking
        const [updatedBooking] = await pool.query('SELECT * FROM bookings WHERE id = ?', [id]);
        
        return formatBooking(updatedBooking[0]);
      } catch (error) {
        console.error('Error completing booking:', error);
        throw new Error(`Failed to complete booking: ${error.message}`);
      }
    }
  },
  
  Booking: {
    user: async (booking) => {
      try {
        const query = `
          query GetUser($id: ID!) {
            user(id: $id) {
              id
              username
              email
              fullName
            }
          }
        `;
        
        const result = await fetchFromService(USER_SERVICE_URL, query, { id: booking.userId });
        return result.user;
      } catch (error) {
        console.error('Error fetching user data:', error);
        return null;
      }
    },
    
    schedule: async (booking) => {
      try {
        const query = `
          query GetSchedule($id: ID!) {
            schedule(id: $id) {
              id
              routeId
              departureTime
              arrivalTime
              busNumber
              capacity
              price
              dayOfWeek
              route {
                id
                name
                startPoint
                endPoint
                distance
                description
              }
            }
          }
        `;
        
        const result = await fetchFromService(ROUTE_SERVICE_URL, query, { id: booking.scheduleId });
        return result.schedule;
      } catch (error) {
        console.error('Error fetching schedule data:', error);
        return null;
      }
    }
  }
};

// Format booking object to match GraphQL schema
function formatBooking(booking) {
  return {
    id: booking.id,
    userId: booking.user_id,
    scheduleId: booking.schedule_id,
    bookingDate: booking.booking_date,
    seatNumber: booking.seat_number,
    status: booking.status,
    createdAt: booking.created_at,
    updatedAt: booking.updated_at,
  };
}

module.exports = { resolvers };
