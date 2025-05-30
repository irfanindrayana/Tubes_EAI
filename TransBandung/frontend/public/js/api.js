/**
 * TransBandung API Client
 * This file provides functions to interact with the GraphQL API gateway
 */

// Base URL for API requests (proxy through Express to avoid CORS issues)
const API_URL = '/graphql';

/**
 * Execute a GraphQL query or mutation
 * @param {string} query - GraphQL query/mutation string
 * @param {Object} variables - Variables for the query/mutation
 * @returns {Promise<Object>} - Response data
 */
async function executeGraphQL(query, variables = {}) {
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': getAuthToken() ? `Bearer ${getAuthToken()}` : ''
      },
      body: JSON.stringify({
        query,
        variables
      })
    });

    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    return result.data;
  } catch (error) {
    console.error('API Error:', error);
    throw error;
  }
}

/**
 * Get auth token from localStorage
 * @returns {string|null} - Auth token or null
 */
function getAuthToken() {
  return localStorage.getItem('auth_token');
}

/**
 * Set auth token in localStorage
 * @param {string} token - Auth token
 */
function setAuthToken(token) {
  localStorage.setItem('auth_token', token);
}

/**
 * Remove auth token from localStorage
 */
function removeAuthToken() {
  localStorage.removeItem('auth_token');
}

/**
 * Check if user is authenticated
 * @returns {boolean} - True if authenticated
 */
function isAuthenticated() {
  return !!getAuthToken();
}

// User API Functions
const userApi = {
  /**
   * Login with username and password
   * @param {string} username - Username
   * @param {string} password - Password
   * @returns {Promise<Object>} - User data with token
   */
  login: async (username, password) => {
    const query = `
      mutation Login($username: String!, $password: String!) {
        login(username: $username, password: $password) {
          token
          user {
            id
            username
            email
            fullName
          }
        }
      }
    `;
    
    const result = await executeGraphQL(query, { username, password });
    if (result.login && result.login.token) {
      setAuthToken(result.login.token);
    }
    return result.login;
  },
  
  /**
   * Register a new user
   * @param {Object} userData - User registration data
   * @returns {Promise<Object>} - Created user data
   */
  register: (userData) => {
    const query = `
      mutation RegisterUser($input: UserInput!) {
        createUser(input: $input) {
          id
          username
          email
          fullName
        }
      }
    `;
    
    return executeGraphQL(query, { input: userData });
  },
  
  /**
   * Get current user profile
   * @returns {Promise<Object>} - User profile data
   */
  getProfile: async () => {
    const query = `
      query GetProfile {
        me {
          id
          username
          email
          fullName
          phoneNumber
        }
      }
    `;
    
    const result = await executeGraphQL(query);
    return result.me;
  },
  
  /**
   * Logout current user
   */
  logout: () => {
    removeAuthToken();
  }
};

// Booking API Functions
const bookingApi = {
  /**
   * Get user's bookings
   * @param {number} userId - User ID
   * @returns {Promise<Array>} - List of bookings
   */
  getUserBookings: async (userId) => {
    const query = `
      query GetUserBookings($userId: ID!) {
        bookingsByUser(userId: $userId) {
          id
          bookingDate
          seatNumber
          status
          schedule {
            id
            departureTime
            arrivalTime
            route {
              name
              startPoint
              endPoint
            }
          }
        }
      }
    `;
    
    const result = await executeGraphQL(query, { userId });
    return result.bookingsByUser;
  },
  
  /**
   * Create a new booking
   * @param {Object} bookingData - Booking data
   * @returns {Promise<Object>} - Created booking
   */
  createBooking: async (bookingData) => {
    const query = `
      mutation CreateBooking($input: BookingInput!) {
        createBooking(input: $input) {
          id
          status
          seatNumber
        }
      }
    `;
    
    const result = await executeGraphQL(query, { input: bookingData });
    return result.createBooking;
  }
};

// Route API Functions
const routeApi = {
  /**
   * Get all routes
   * @returns {Promise<Array>} - List of routes
   */
  getAllRoutes: async () => {
    const query = `
      query GetRoutes {
        routes {
          id
          name
          startPoint
          endPoint
          distance
          schedules {
            id
            departureTime
            arrivalTime
            price
            busNumber
            capacity
          }
        }
      }
    `;
    
    const result = await executeGraphQL(query);
    return result.routes;
  },
  
  /**
   * Find schedules by route ID and day
   * @param {number} routeId - Route ID
   * @param {string} dayOfWeek - Day of week
   * @returns {Promise<Array>} - List of schedules
   */
  findSchedules: async (routeId, dayOfWeek) => {
    const query = `
      query FindSchedules($routeId: ID!, $dayOfWeek: String) {
        schedules(routeId: $routeId, dayOfWeek: $dayOfWeek) {
          id
          departureTime
          arrivalTime
          busNumber
          capacity
          price
          dayOfWeek
        }
      }
    `;
    
    const result = await executeGraphQL(query, { routeId, dayOfWeek });
    return result.schedules;
  }
};

// Payment API Functions
const paymentApi = {
  /**
   * Get payment by booking ID
   * @param {number} bookingId - Booking ID
   * @returns {Promise<Object>} - Payment data
   */
  getPaymentByBookingId: async (bookingId) => {
    const query = `
      query GetPayment($bookingId: ID!) {
        paymentByBookingId(bookingId: $bookingId) {
          id
          amount
          paymentMethod
          status
          paymentDate
          transactionId
        }
      }
    `;
    
    const result = await executeGraphQL(query, { bookingId });
    return result.paymentByBookingId;
  },
  
  /**
   * Create a new payment
   * @param {Object} paymentData - Payment data
   * @returns {Promise<Object>} - Created payment
   */
  createPayment: async (paymentData) => {
    const query = `
      mutation CreatePayment($input: PaymentInput!) {
        createPayment(input: $input) {
          id
          status
          transactionId
        }
      }
    `;
    
    const result = await executeGraphQL(query, { input: paymentData });
    return result.createPayment;
  }
};

// Review API Functions
const reviewApi = {
  /**
   * Get reviews by booking ID
   * @param {number} bookingId - Booking ID
   * @returns {Promise<Array>} - List of reviews
   */
  getReviewsByBookingId: async (bookingId) => {
    const query = `
      query GetReviews($bookingId: ID!) {
        reviewsByBookingId(bookingId: $bookingId) {
          id
          rating
          comment
          user {
            username
          }
        }
      }
    `;
    
    const result = await executeGraphQL(query, { bookingId });
    return result.reviewsByBookingId;
  },
  
  /**
   * Create a new review
   * @param {Object} reviewData - Review data
   * @returns {Promise<Object>} - Created review
   */
  createReview: async (reviewData) => {
    const query = `
      mutation AddReview($input: ReviewInput!) {
        createReview(input: $input) {
          id
          rating
          comment
        }
      }
    `;
    
    const result = await executeGraphQL(query, { input: reviewData });
    return result.createReview;
  }
};

// Export the API client
window.transBandungApi = {
  user: userApi,
  booking: bookingApi,
  route: routeApi,
  payment: paymentApi,
  review: reviewApi,
  utils: {
    isAuthenticated,
    getAuthToken,
    setAuthToken,
    removeAuthToken
  }
};
