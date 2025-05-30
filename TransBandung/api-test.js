#!/usr/bin/env node

/**
 * TransBandung API Gateway Test Script
 * 
 * This script tests the connectivity and functionality of the API Gateway
 * and its integration with the microservices.
 */

const fetch = require('node-fetch');
const chalk = require('chalk');

// Configuration
const API_URL = process.env.API_URL || 'http://localhost:4000/graphql';
let authToken = null;

// Test user credentials
const TEST_USER = {
  username: 'testuser',
  password: 'password123',
  email: 'test@example.com',
  fullName: 'Test User'
};

// Run tests
async function runTests() {
  console.log(chalk.blue('🚌 TransBandung API Gateway Test Suite'));
  console.log(chalk.blue('======================================'));
  
  try {
    // Health check
    await testHealthCheck();
    
    // User service tests
    await testUserRegistration();
    await testUserLogin();
    await testGetUserProfile();
    
    // Route service tests
    await testGetRoutes();
    await testGetRouteById();
    
    // Booking service tests
    await testCreateBooking();
    await testGetUserBookings();
    
    // Review service tests
    await testCreateReview();
    await testGetReviews();
    
    // Payment service tests
    await testCreatePayment();
    
    console.log('\n' + chalk.green('✅ All tests completed successfully!'));
    
  } catch (error) {
    console.error('\n' + chalk.red(`❌ Tests failed: ${error.message}`));
    process.exit(1);
  }
}

// Health check
async function testHealthCheck() {
  console.log('\n' + chalk.yellow('Testing API Gateway Health...'));
  
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        query: `{ __typename }`
      }),
    });
    
    const result = await response.json();
    
    if (response.ok && result.data) {
      console.log(chalk.green('✅ API Gateway is healthy'));
    } else {
      throw new Error('API gateway responded with errors');
    }
  } catch (error) {
    console.error(chalk.red(`❌ Health check failed: ${error.message}`));
    throw error;
  }
}

// Test user registration
async function testUserRegistration() {
  console.log('\n' + chalk.yellow('Testing User Registration...'));
  
  try {
    // First, try to delete the test user if it exists (cleanup)
    try {
      await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          query: `
            mutation {
              deleteUserByUsername(username: "${TEST_USER.username}")
            }
          `
        }),
      });
    } catch (e) {
      // Ignore errors here - user might not exist
    }
    
    // Now register a new user
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        query: `
          mutation {
            createUser(input: {
              username: "${TEST_USER.username}",
              password: "${TEST_USER.password}",
              email: "${TEST_USER.email}",
              fullName: "${TEST_USER.fullName}",
              userType: customer
            }) {
              id
              username
              fullName
            }
          }
        `
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    if (result.data.createUser.username === TEST_USER.username) {
      console.log(chalk.green(`✅ Successfully registered user: ${result.data.createUser.username}`));
    } else {
      throw new Error('User registration failed');
    }
    
  } catch (error) {
    console.error(chalk.red(`❌ User registration failed: ${error.message}`));
    throw error;
  }
}

// Test user login
async function testUserLogin() {
  console.log('\n' + chalk.yellow('Testing User Login...'));
  
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        query: `
          mutation {
            login(username: "${TEST_USER.username}", password: "${TEST_USER.password}") {
              token
              user {
                id
                username
              }
            }
          }
        `
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    authToken = result.data.login.token;
    
    if (authToken) {
      console.log(chalk.green('✅ Successfully logged in and received auth token'));
    } else {
      throw new Error('Login successful but no token received');
    }
    
  } catch (error) {
    console.error(chalk.red(`❌ User login failed: ${error.message}`));
    throw error;
  }
}

// Test getting user profile
async function testGetUserProfile() {
  console.log('\n' + chalk.yellow('Testing Get User Profile...'));
  
  if (!authToken) {
    console.warn(chalk.yellow('⚠️ Skipping test - no auth token available'));
    return;
  }
  
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authToken}`
      },
      body: JSON.stringify({
        query: `
          query {
            me {
              id
              username
              fullName
              email
            }
          }
        `
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    if (result.data.me.username === TEST_USER.username) {
      console.log(chalk.green('✅ Successfully retrieved user profile'));
    } else {
      throw new Error('Retrieved profile does not match test user');
    }
    
  } catch (error) {
    console.error(chalk.red(`❌ Get user profile failed: ${error.message}`));
    throw error;
  }
}

// Test getting routes
async function testGetRoutes() {
  console.log('\n' + chalk.yellow('Testing Get Routes...'));
  
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        query: `
          query {
            routes {
              id
              routeName
              startPoint
              endPoint
            }
          }
        `
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    console.log(chalk.green(`✅ Successfully retrieved ${result.data.routes.length} routes`));
    
  } catch (error) {
    console.error(chalk.red(`❌ Get routes failed: ${error.message}`));
    throw error;
  }
}

// Helper functions for other tests
async function testGetRouteById() {
  console.log('\n' + chalk.yellow('Testing Get Route By ID...'));
  
  try {
    // First get all routes
    const routesResponse = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        query: `
          query {
            routes {
              id
              routeName
            }
          }
        `
      }),
    });
    
    const routesResult = await routesResponse.json();
    
    if (routesResult.errors) {
      throw new Error(routesResult.errors[0].message);
    }
    
    if (routesResult.data.routes.length === 0) {
      console.warn(chalk.yellow('⚠️ No routes available to test with'));
      return null;
    }
    
    // Get the first route ID
    const routeId = routesResult.data.routes[0].id;
    
    // Now get that specific route
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        query: `
          query {
            route(id: "${routeId}") {
              id
              routeName
              startPoint
              endPoint
            }
          }
        `
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    if (result.data.route.id === routeId) {
      console.log(chalk.green(`✅ Successfully retrieved route by ID: ${result.data.route.routeName}`));
      return routeId;
    } else {
      throw new Error('Retrieved route does not match requested ID');
    }
    
  } catch (error) {
    console.error(chalk.red(`❌ Get route by ID failed: ${error.message}`));
    throw error;
  }
}

// Test creating a booking
async function testCreateBooking() {
  console.log('\n' + chalk.yellow('Testing Create Booking...'));
  
  if (!authToken) {
    console.warn(chalk.yellow('⚠️ Skipping test - no auth token available'));
    return;
  }
  
  try {
    // Get a route first
    const routeId = await testGetRouteById();
    
    if (!routeId) {
      console.warn(chalk.yellow('⚠️ Skipping test - no routes available'));
      return;
    }
    
    // Get schedules for this route
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];
    
    const schedulesResponse = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        query: `
          query {
            schedulesByRoute(routeId: "${routeId}", date: "${tomorrowStr}") {
              id
              departureTime
              availableSeats
            }
          }
        `
      }),
    });
    
    const schedulesResult = await schedulesResponse.json();
    
    if (schedulesResult.errors) {
      throw new Error(schedulesResult.errors[0].message);
    }
    
    if (schedulesResult.data.schedulesByRoute.length === 0) {
      console.warn(chalk.yellow('⚠️ No schedules available for testing'));
      return;
    }
    
    const scheduleId = schedulesResult.data.schedulesByRoute[0].id;
    
    // Create a booking
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authToken}`
      },
      body: JSON.stringify({
        query: `
          mutation {
            createBooking(input: {
              userId: "current-user",
              scheduleId: "${scheduleId}",
              seatNumbers: ["A1"],
              totalPrice: 10000,
              passengers: [{name: "Test Passenger", idNumber: "1234567890123456"}]
            }) {
              id
              bookingNumber
              status
            }
          }
        `
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    console.log(chalk.green(`✅ Successfully created booking: ${result.data.createBooking.bookingNumber}`));
    return result.data.createBooking.id;
    
  } catch (error) {
    console.error(chalk.red(`❌ Create booking failed: ${error.message}`));
    throw error;
  }
}

// Test getting user bookings
async function testGetUserBookings() {
  console.log('\n' + chalk.yellow('Testing Get User Bookings...'));
  
  if (!authToken) {
    console.warn(chalk.yellow('⚠️ Skipping test - no auth token available'));
    return;
  }
  
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authToken}`
      },
      body: JSON.stringify({
        query: `
          query {
            myBookings {
              id
              bookingNumber
              status
              totalPrice
            }
          }
        `
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    console.log(chalk.green(`✅ Successfully retrieved ${result.data.myBookings.length} user bookings`));
    
    if (result.data.myBookings.length > 0) {
      return result.data.myBookings[0].id;
    }
    return null;
    
  } catch (error) {
    console.error(chalk.red(`❌ Get user bookings failed: ${error.message}`));
    throw error;
  }
}

// Test creating a payment
async function testCreatePayment() {
  console.log('\n' + chalk.yellow('Testing Create Payment...'));
  
  if (!authToken) {
    console.warn(chalk.yellow('⚠️ Skipping test - no auth token available'));
    return;
  }
  
  try {
    // Get a booking ID first
    const bookingId = await testGetUserBookings();
    
    if (!bookingId) {
      console.warn(chalk.yellow('⚠️ Skipping test - no bookings available'));
      return;
    }
    
    // Create payment
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authToken}`
      },
      body: JSON.stringify({
        query: `
          mutation {
            createPayment(input: {
              bookingId: "${bookingId}",
              amount: 10000,
              paymentMethod: "bank_transfer",
              bank: "BCA",
              paymentProof: "base64encodedstring"
            }) {
              id
              status
            }
          }
        `
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    console.log(chalk.green(`✅ Successfully created payment with status: ${result.data.createPayment.status}`));
    
  } catch (error) {
    console.error(chalk.red(`❌ Create payment failed: ${error.message}`));
    throw error;
  }
}

// Test creating a review
async function testCreateReview() {
  console.log('\n' + chalk.yellow('Testing Create Review...'));
  
  if (!authToken) {
    console.warn(chalk.yellow('⚠️ Skipping test - no auth token available'));
    return;
  }
  
  try {
    // Get a booking ID first
    const bookingId = await testGetUserBookings();
    
    if (!bookingId) {
      console.warn(chalk.yellow('⚠️ Skipping test - no bookings available'));
      return;
    }
    
    // Create review
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authToken}`
      },
      body: JSON.stringify({
        query: `
          mutation {
            createReview(input: {
              userId: "current-user",
              bookingId: "${bookingId}",
              rating: 5,
              comment: "Great service!"
            }) {
              id
              rating
            }
          }
        `
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    console.log(chalk.green(`✅ Successfully created review with rating: ${result.data.createReview.rating}`));
    
  } catch (error) {
    console.error(chalk.red(`❌ Create review failed: ${error.message}`));
    throw error;
  }
}

// Test getting reviews
async function testGetReviews() {
  console.log('\n' + chalk.yellow('Testing Get Reviews...'));
  
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        query: `
          query {
            reviews {
              id
              rating
              comment
              user {
                fullName
              }
            }
          }
        `
      }),
    });
    
    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    console.log(chalk.green(`✅ Successfully retrieved ${result.data.reviews.length} reviews`));
    
  } catch (error) {
    console.error(chalk.red(`❌ Get reviews failed: ${error.message}`));
    throw error;
  }
}

// Run the tests
runTests();
