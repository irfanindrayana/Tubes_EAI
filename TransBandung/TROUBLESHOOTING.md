# Troubleshooting.md - TransBandung

## Common Issues and Solutions

### 1. Docker Hub Connection Issues

#### Symptom: 
Error when building images, especially with `nginx:alpine`:
```
ERROR [frontend internal] load metadata for docker.io/library/nginx:alpine
failed to solve: nginx:alpine: failed to resolve source metadata for docker.io/library/nginx:alpine: failed to authorize: failed to fetch oauth token: Post "https://auth.docker.io/token": EOF
```

#### Solutions:

1. **Use the provided build script**
   ```powershell
   .\build-containers.ps1
   ```
   This script includes retry mechanisms and pre-pulls required images.

2. **Log in to Docker Hub**
   ```powershell
   docker login
   ```
   This can help avoid rate limiting for anonymous pulls.

3. **Use a specific version instead of 'alpine' tag**
   We've updated the Dockerfile to use `nginx:1.25.1` instead of `nginx:alpine`.

4. **Run the frontend locally (without Docker)**
   If you're still having issues with Docker Hub:
   ```powershell
   cd frontend
   # If you have Python:
   python -m http.server 80
   # OR if you have PHP:
   php -S 0.0.0.0:80
   # OR if you have Node.js:
   npx serve -l 80
   ```

5. **Check your internet connection**
   - Make sure your internet connection is stable
   - Check if you're behind a restrictive firewall or proxy
   - Try using a different network if possible

### 2. Database Connection Issues

#### Symptom:
Services fail to connect to the MySQL database.

#### Solutions:

1. **Check if MySQL container is running**
   ```powershell
   docker ps | Select-String "mysql"
   ```

2. **Check MySQL logs**
   ```powershell
   docker logs transbandung-mysql
   ```

3. **Ensure the database initialization was successful**
   ```powershell
   docker exec -it transbandung-mysql mysql -e "SHOW DATABASES;"
   ```

### 3. API Gateway Connection Issues

#### Symptom:
Frontend can't connect to the backend services through the API Gateway.

#### Solutions:

1. **Check if API Gateway is running**
   ```powershell
   docker ps | Select-String "api-gateway"
   ```

2. **Test the API Gateway directly**
   Open http://localhost:4000/graphql in your browser and try a simple query:
   ```graphql
   {
     __typename
   }
   ```

3. **Check API Gateway logs**
   ```powershell
   docker logs transbandung-api-gateway
   ```

### 4. CORS Issues

#### Symptom:
Frontend makes requests, but gets CORS errors in the browser console.

#### Solution:
Make sure the API Gateway has proper CORS configuration (already included in our updates).

### 5. Docker Volume Persistence Issues

#### Symptom:
Data doesn't persist after container restart.

#### Solution:
Check if the MySQL data volume is properly mounted:
```powershell
docker volume ls | Select-String "mysql-data"
```

### Contact Support

If you continue to experience issues, please contact support at:
- Email: support@transbandung.com
- GitHub Issues: https://github.com/transbandung/issues
