const express = require('express');
const path = require('path');
const { createProxyMiddleware } = require('http-proxy-middleware');
const app = express();
const PORT = 3000;

// API Gateway URL from environment variable or default to localhost
const API_GATEWAY_URL = process.env.API_GATEWAY_URL || 'http://localhost:4000/graphql';

app.use(express.static(path.join(__dirname, '../public')));
app.use(express.json());

// Setup proxy for GraphQL requests
app.use('/graphql', createProxyMiddleware({
  target: API_GATEWAY_URL,
  changeOrigin: true,
  pathRewrite: {
    '^/graphql': '/'
  }
}));

// Serve HTML pages
app.get('/', (req, res) => res.sendFile(path.join(__dirname, '../public/index.html')));
app.get('/user', (req, res) => res.sendFile(path.join(__dirname, '../public/user.html')));
app.get('/booking', (req, res) => res.sendFile(path.join(__dirname, '../public/booking.html')));
app.get('/route', (req, res) => res.sendFile(path.join(__dirname, '../public/route.html')));
app.get('/review', (req, res) => res.sendFile(path.join(__dirname, '../public/review.html')));
app.get('/payment', (req, res) => res.sendFile(path.join(__dirname, '../public/payment.html')));

app.listen(PORT, () => {
  console.log(`Frontend running at http://localhost:${PORT}`);
  console.log(`API Gateway proxy configured to ${API_GATEWAY_URL}`);
});