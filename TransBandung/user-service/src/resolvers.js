const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const { getDB } = require('./db');

// Secret key for JWT
const JWT_SECRET = 'transbandung-secret-key';

const resolvers = {
  Query: {
    user: async (_, { id }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM users WHERE id = ?', [id]);
        if (rows.length === 0) return null;
        return formatUser(rows[0]);
      } catch (error) {
        console.error('Error fetching user:', error);
        throw new Error('Failed to fetch user');
      }
    },
    
    userByUsername: async (_, { username }) => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM users WHERE username = ?', [username]);
        if (rows.length === 0) return null;
        return formatUser(rows[0]);
      } catch (error) {
        console.error('Error fetching user by username:', error);
        throw new Error('Failed to fetch user by username');
      }
    },
    
    users: async () => {
      try {
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM users');
        return rows.map(user => formatUser(user));
      } catch (error) {
        console.error('Error fetching users:', error);
        throw new Error('Failed to fetch users');
      }
    },
    
    me: async (_, __, { token }) => {
      if (!token) return null;
      
      // Extract the token from the authorization header
      const tokenParts = token.split(' ');
      if (tokenParts.length !== 2 || tokenParts[0] !== 'Bearer') return null;
      
      try {
        const decoded = jwt.verify(tokenParts[1], JWT_SECRET);
        const pool = getDB();
        const [rows] = await pool.query('SELECT * FROM users WHERE id = ?', [decoded.userId]);
        if (rows.length === 0) return null;
        return formatUser(rows[0]);
      } catch (error) {
        console.error('Error verifying token:', error);
        return null;
      }
    }
  },
  
  Mutation: {
    createUser: async (_, { input }) => {
      const { username, password, email, fullName, phoneNumber, userType = 'customer' } = input;
      
      try {
        // Hash the password
        const hashedPassword = await bcrypt.hash(password, 10);
        
        const pool = getDB();
        
        // Check if username or email already exists
        const [existingUsers] = await pool.query(
          'SELECT * FROM users WHERE username = ? OR email = ?',
          [username, email]
        );
        
        if (existingUsers.length > 0) {
          throw new Error('Username or email already exists');
        }
        
        // Insert new user
        const [result] = await pool.query(
          'INSERT INTO users (username, password, email, full_name, phone_number, user_type) VALUES (?, ?, ?, ?, ?, ?)',
          [username, hashedPassword, email, fullName, phoneNumber, userType]
        );
        
        const [newUser] = await pool.query('SELECT * FROM users WHERE id = ?', [result.insertId]);
        
        return formatUser(newUser[0]);
      } catch (error) {
        console.error('Error creating user:', error);
        throw new Error(`Failed to create user: ${error.message}`);
      }
    },
    
    updateUser: async (_, { id, input }) => {
      try {
        const pool = getDB();
        
        // Check if user exists
        const [existingUser] = await pool.query('SELECT * FROM users WHERE id = ?', [id]);
        if (existingUser.length === 0) {
          throw new Error('User not found');
        }
        
        // Prepare update fields
        const updateFields = [];
        const updateValues = [];
        
        if (input.username) {
          updateFields.push('username = ?');
          updateValues.push(input.username);
        }
        
        if (input.password) {
          const hashedPassword = await bcrypt.hash(input.password, 10);
          updateFields.push('password = ?');
          updateValues.push(hashedPassword);
        }
        
        if (input.email) {
          updateFields.push('email = ?');
          updateValues.push(input.email);
        }
        
        if (input.fullName) {
          updateFields.push('full_name = ?');
          updateValues.push(input.fullName);
        }
        
        if (input.phoneNumber) {
          updateFields.push('phone_number = ?');
          updateValues.push(input.phoneNumber);
        }
        
        if (input.userType) {
          updateFields.push('user_type = ?');
          updateValues.push(input.userType);
        }
        
        if (updateFields.length === 0) {
          throw new Error('No fields to update');
        }
        
        // Update user
        await pool.query(
          `UPDATE users SET ${updateFields.join(', ')} WHERE id = ?`,
          [...updateValues, id]
        );
        
        // Get updated user
        const [updatedUser] = await pool.query('SELECT * FROM users WHERE id = ?', [id]);
        
        return formatUser(updatedUser[0]);
      } catch (error) {
        console.error('Error updating user:', error);
        throw new Error(`Failed to update user: ${error.message}`);
      }
    },
    
    deleteUser: async (_, { id }) => {
      try {
        const pool = getDB();
        
        // Check if user exists
        const [existingUser] = await pool.query('SELECT * FROM users WHERE id = ?', [id]);
        if (existingUser.length === 0) {
          throw new Error('User not found');
        }
        
        // Delete user
        await pool.query('DELETE FROM users WHERE id = ?', [id]);
        
        return true;
      } catch (error) {
        console.error('Error deleting user:', error);
        throw new Error(`Failed to delete user: ${error.message}`);
      }
    },
    
    login: async (_, { username, password }) => {
      try {
        const pool = getDB();
        
        // Find user by username
        const [users] = await pool.query('SELECT * FROM users WHERE username = ?', [username]);
        
        if (users.length === 0) {
          throw new Error('Invalid username or password');
        }
        
        const user = users[0];
        
        // Compare passwords
        const isPasswordValid = await bcrypt.compare(password, user.password);
        
        if (!isPasswordValid) {
          throw new Error('Invalid username or password');
        }
        
        // Generate JWT token
        const token = jwt.sign(
          { userId: user.id, userType: user.user_type },
          JWT_SECRET,
          { expiresIn: '24h' }
        );
        
        return {
          token,
          user: formatUser(user),
        };
      } catch (error) {
        console.error('Error during login:', error);
        throw new Error(`Login failed: ${error.message}`);
      }
    }
  }
};

// Format user object to match GraphQL schema
function formatUser(user) {
  return {
    id: user.id,
    username: user.username,
    email: user.email,
    fullName: user.full_name,
    phoneNumber: user.phone_number,
    userType: user.user_type,
    createdAt: user.created_at,
    updatedAt: user.updated_at,
  };
}

module.exports = { resolvers };
