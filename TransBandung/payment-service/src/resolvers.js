const fetch = require('node-fetch');
const { v4: uuidv4 } = require('uuid');
const { getDB } = require('./db');

// Service URLs
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
    payment: async (_, { id }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM payments WHERE id = ?', [id]);
        if (rows.length === 0) return null;
        return formatPayment(rows[0]);
      } catch (error) {
        console.error('Error fetching payment:', error);
        throw new Error('Failed to fetch payment');
      }
    },
    
    payments: async () => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM payments');
        return rows.map(payment => formatPayment(payment));
      } catch (error) {
        console.error('Error fetching payments:', error);
        throw new Error('Failed to fetch payments');
      }
    },
    
    paymentByBookingId: async (_, { bookingId }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM payments WHERE booking_id = ?', [bookingId]);
        if (rows.length === 0) return null;
        return formatPayment(rows[0]);
      } catch (error) {
        console.error('Error fetching payment by booking ID:', error);
        throw new Error('Failed to fetch payment by booking ID');
      }
    },
    
    paymentsByStatus: async (_, { status }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM payments WHERE status = ?', [status]);
        return rows.map(payment => formatPayment(payment));
      } catch (error) {
        console.error('Error fetching payments by status:', error);
        throw new Error('Failed to fetch payments by status');
      }
    }
  },
  
  Mutation: {
    createPayment: async (_, { input }) => {
      const { bookingId, amount, paymentMethod } = input;
      
      try {
        const pool = getDB();
        
        // Check if payment already exists for this booking
        const [existingPayments] = await pool.query(
          'SELECT * FROM payments WHERE booking_id = ?',
          [bookingId]
        );
        
        if (existingPayments.length > 0) {
          throw new Error('Payment already exists for this booking');
        }
        
        // Generate a transaction ID (in a real system this would come from the payment gateway)
        const transactionId = `TRX-${uuidv4().substring(0, 8)}`;
        
        // Insert payment record with pending status
        const [result] = await pool.query(
          'INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, "pending")',
          [bookingId, amount, paymentMethod, transactionId]
        );
        
        const [newPayment] = await pool.query('SELECT * FROM payments WHERE id = ?', [result.insertId]);
        
        return formatPayment(newPayment[0]);
      } catch (error) {
        console.error('Error creating payment:', error);
        throw new Error(`Failed to create payment: ${error.message}`);
      }
    },
    
    updatePayment: async (_, { id, input }) => {
      try {
        const pool = getDB();
        
        // Check if payment exists
        const [existingPayment] = await pool.query('SELECT * FROM payments WHERE id = ?', [id]);
        if (existingPayment.length === 0) {
          throw new Error('Payment not found');
        }
        
        // Prepare update fields
        const updateFields = [];
        const updateValues = [];
        
        if (input.status) {
          updateFields.push('status = ?');
          updateValues.push(input.status);
        }
        
        if (input.transactionId) {
          updateFields.push('transaction_id = ?');
          updateValues.push(input.transactionId);
        }
        
        if (input.paymentDate) {
          updateFields.push('payment_date = ?');
          updateValues.push(input.paymentDate);
        }
        
        if (updateFields.length === 0) {
          throw new Error('No fields to update');
        }
        
        // Update payment
        await pool.query(
          `UPDATE payments SET ${updateFields.join(', ')} WHERE id = ?`,
          [...updateValues, id]
        );
        
        // Get updated payment
        const [updatedPayment] = await pool.query('SELECT * FROM payments WHERE id = ?', [id]);
        
        return formatPayment(updatedPayment[0]);
      } catch (error) {
        console.error('Error updating payment:', error);
        throw new Error(`Failed to update payment: ${error.message}`);
      }
    },
    
    processPayment: async (_, { id }) => {
      try {
        const pool = getDB();
        
        // Check if payment exists
        const [existingPayment] = await pool.query('SELECT * FROM payments WHERE id = ?', [id]);
        if (existingPayment.length === 0) {
          throw new Error('Payment not found');
        }
        
        // Check if payment is already completed
        if (existingPayment[0].status === 'completed') {
          throw new Error('Payment is already completed');
        }
        
        // Check if payment is already failed
        if (existingPayment[0].status === 'failed') {
          throw new Error('Payment has already failed and cannot be processed');
        }
        
        // Simulate a payment processing
        // In a real system, this would interact with a payment gateway
        const isSuccessful = Math.random() > 0.1; // 90% success rate
        
        // Update payment status and payment date
        const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
        await pool.query(
          'UPDATE payments SET status = ?, payment_date = ? WHERE id = ?',
          [isSuccessful ? 'completed' : 'failed', isSuccessful ? now : null, id]
        );
        
        // If payment is successful, update the booking status to confirmed
        if (isSuccessful) {
          // Update booking status through Booking Service
          try {
            const bookingId = existingPayment[0].booking_id;
            const mutation = `
              mutation UpdateBooking($id: ID!, $input: UpdateBookingInput!) {
                updateBooking(id: $id, input: $input) {
                  id
                  status
                }
              }
            `;
            
            await fetchFromService(BOOKING_SERVICE_URL, mutation, {
              id: bookingId,
              input: { status: 'confirmed' }
            });
          } catch (error) {
            console.error('Error updating booking status:', error);
            // Continue execution, don't block payment processing if booking update fails
          }
        }
        
        // Get updated payment
        const [updatedPayment] = await pool.query('SELECT * FROM payments WHERE id = ?', [id]);
        
        return formatPayment(updatedPayment[0]);
      } catch (error) {
        console.error('Error processing payment:', error);
        throw new Error(`Failed to process payment: ${error.message}`);
      }
    },
    
    refundPayment: async (_, { id }) => {
      try {
        const pool = getDB();
        
        // Check if payment exists
        const [existingPayment] = await pool.query('SELECT * FROM payments WHERE id = ?', [id]);
        if (existingPayment.length === 0) {
          throw new Error('Payment not found');
        }
        
        // Check if payment is completed
        if (existingPayment[0].status !== 'completed') {
          throw new Error('Only completed payments can be refunded');
        }
        
        // Update payment status to refunded
        await pool.query('UPDATE payments SET status = "refunded" WHERE id = ?', [id]);
        
        // Update booking status to cancelled
        try {
          const bookingId = existingPayment[0].booking_id;
          const mutation = `
            mutation CancelBooking($id: ID!) {
              cancelBooking(id: $id) {
                id
                status
              }
            }
          `;
          
          await fetchFromService(BOOKING_SERVICE_URL, mutation, { id: bookingId });
        } catch (error) {
          console.error('Error updating booking status:', error);
          // Continue execution, don't block refund if booking update fails
        }
        
        // Get updated payment
        const [updatedPayment] = await pool.query('SELECT * FROM payments WHERE id = ?', [id]);
        
        return formatPayment(updatedPayment[0]);
      } catch (error) {
        console.error('Error refunding payment:', error);
        throw new Error(`Failed to refund payment: ${error.message}`);
      }
    }
  },
  
  Payment: {
    booking: async (payment) => {
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
        
        const result = await fetchFromService(BOOKING_SERVICE_URL, query, { id: payment.bookingId });
        return result.booking;
      } catch (error) {
        console.error('Error fetching booking data:', error);
        return null;
      }
    }
  }
};

// Format payment object to match GraphQL schema
function formatPayment(payment) {
  return {
    id: payment.id,
    bookingId: payment.booking_id,
    amount: parseFloat(payment.amount),
    paymentMethod: payment.payment_method,
    transactionId: payment.transaction_id,
    status: payment.status,
    paymentDate: payment.payment_date,
    createdAt: payment.created_at,
    updatedAt: payment.updated_at,
  };
}

module.exports = { resolvers };
