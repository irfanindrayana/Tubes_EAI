const fetch = require('node-fetch');
const { getDB } = require('./db');

// Service URLs
const USER_SERVICE_URL = process.env.USER_SERVICE_URL || 'http://localhost:4001/graphql';
const BOOKING_SERVICE_URL = process.env.BOOKING_SERVICE_URL || 'http://localhost:4002/graphql';

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
    review: async (_, { id }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM reviews WHERE id = ?', [id]);
        if (rows.length === 0) return null;
        return formatReview(rows[0]);
      } catch (error) {
        console.error('Error fetching review:', error);
        throw new Error('Failed to fetch review');
      }
    },
    
    reviews: async () => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM reviews');
        return rows.map(review => formatReview(review));
      } catch (error) {
        console.error('Error fetching reviews:', error);
        throw new Error('Failed to fetch reviews');
      }
    },
    
    reviewsByUser: async (_, { userId }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM reviews WHERE user_id = ?', [userId]);
        return rows.map(review => formatReview(review));
      } catch (error) {
        console.error('Error fetching reviews by user:', error);
        throw new Error('Failed to fetch reviews by user');
      }
    },
    
    reviewsByBookingId: async (_, { bookingId }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM reviews WHERE booking_id = ?', [bookingId]);
        return rows.map(review => formatReview(review));
      } catch (error) {
        console.error('Error fetching reviews by booking:', error);
        throw new Error('Failed to fetch reviews by booking');
      }
    },
    
    averageRating: async () => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT AVG(rating) as average FROM reviews');
        return rows[0].average || 0;
      } catch (error) {
        console.error('Error calculating average rating:', error);
        throw new Error('Failed to calculate average rating');
      }
    }
  },
  
  Mutation: {
    createReview: async (_, { input }) => {
      const { userId, bookingId, rating, comment } = input;
      
      try {
        const pool = getDB();
        
        // Check if rating is valid (1-5)
        if (rating < 1 || rating > 5) {
          throw new Error('Rating must be between 1 and 5');
        }
        
        // Check if user has already reviewed this booking
        const [existingReviews] = await pool.query(
          'SELECT * FROM reviews WHERE user_id = ? AND booking_id = ?',
          [userId, bookingId]
        );
        
        if (existingReviews.length > 0) {
          throw new Error('User has already reviewed this booking');
        }
        
        // Insert review
        const [result] = await pool.query(
          'INSERT INTO reviews (user_id, booking_id, rating, comment) VALUES (?, ?, ?, ?)',
          [userId, bookingId, rating, comment]
        );
        
        const [newReview] = await pool.query('SELECT * FROM reviews WHERE id = ?', [result.insertId]);
        
        return formatReview(newReview[0]);
      } catch (error) {
        console.error('Error creating review:', error);
        throw new Error(`Failed to create review: ${error.message}`);
      }
    },
    
    updateReview: async (_, { id, input }) => {
      try {
        const pool = getDB();
        
        // Check if review exists
        const [existingReview] = await pool.query('SELECT * FROM reviews WHERE id = ?', [id]);
        if (existingReview.length === 0) {
          throw new Error('Review not found');
        }
        
        // Prepare update fields
        const updateFields = [];
        const updateValues = [];
        
        if (input.rating !== undefined) {
          if (input.rating < 1 || input.rating > 5) {
            throw new Error('Rating must be between 1 and 5');
          }
          updateFields.push('rating = ?');
          updateValues.push(input.rating);
        }
        
        if (input.comment !== undefined) {
          updateFields.push('comment = ?');
          updateValues.push(input.comment);
        }
        
        if (updateFields.length === 0) {
          throw new Error('No fields to update');
        }
        
        // Update review
        await pool.query(
          `UPDATE reviews SET ${updateFields.join(', ')} WHERE id = ?`,
          [...updateValues, id]
        );
        
        // Get updated review
        const [updatedReview] = await pool.query('SELECT * FROM reviews WHERE id = ?', [id]);
        
        return formatReview(updatedReview[0]);
      } catch (error) {
        console.error('Error updating review:', error);
        throw new Error(`Failed to update review: ${error.message}`);
      }
    },
    
    deleteReview: async (_, { id }) => {
      try {
        const pool = getDB();
        
        // Check if review exists
        const [existingReview] = await pool.query('SELECT * FROM reviews WHERE id = ?', [id]);
        if (existingReview.length === 0) {
          throw new Error('Review not found');
        }
        
        // Delete review
        await pool.query('DELETE FROM reviews WHERE id = ?', [id]);
        
        return true;
      } catch (error) {
        console.error('Error deleting review:', error);
        throw new Error(`Failed to delete review: ${error.message}`);
      }
    }
  },
  
  Review: {
    user: async (review) => {
      try {
        const query = `
          query GetUser($id: ID!) {
            user(id: $id) {
              id
              username
              fullName
            }
          }
        `;
        
        const result = await fetchFromService(USER_SERVICE_URL, query, { id: review.userId });
        return result.user;
      } catch (error) {
        console.error('Error fetching user data:', error);
        return null;
      }
    },
    
    booking: async (review) => {
      try {
        const query = `
          query GetBooking($id: ID!) {
            booking(id: $id) {
              id
              userId
              scheduleId
              bookingDate
              status
            }
          }
        `;
        
        const result = await fetchFromService(BOOKING_SERVICE_URL, query, { id: review.bookingId });
        return result.booking;
      } catch (error) {
        console.error('Error fetching booking data:', error);
        return null;
      }
    }
  }
};

// Format review object to match GraphQL schema
function formatReview(review) {
  return {
    id: review.id,
    userId: review.user_id,
    bookingId: review.booking_id,
    rating: review.rating,
    comment: review.comment,
    createdAt: review.created_at,
    updatedAt: review.updated_at,
  };
}

module.exports = { resolvers };
