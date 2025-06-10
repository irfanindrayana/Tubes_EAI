# Dockerfile untuk Vite Development Server
FROM node:18-alpine

WORKDIR /var/www/html

# Install dependencies
COPY package*.json ./
RUN npm install

# Copy application files
COPY . .

# Expose port for Vite dev server
EXPOSE 5173

# Start Vite development server
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
