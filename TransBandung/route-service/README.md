# Route Schedule Service for TransBandung

This service manages bus routes and schedules for the TransBandung system.

## Features
- Create, update, and delete routes
- Create, update, and delete schedules
- Search routes by name, start point, or end point
- Filter schedules by route and day of week

## GraphQL Schema

### Types
- `Route`: Represents a bus route
- `Schedule`: Represents a bus schedule

### Queries
- `route(id: ID!)`: Get route by ID
- `routes`: Get all routes
- `searchRoutes(query: String!)`: Search routes by name, start point, or end point
- `schedule(id: ID!)`: Get schedule by ID
- `schedules(routeId: ID, dayOfWeek: DayOfWeek)`: Get schedules with optional filters
- `schedulesByRoute(routeId: ID!)`: Get schedules for a specific route
- `schedulesByDay(dayOfWeek: DayOfWeek!)`: Get schedules for a specific day of the week

### Mutations
- `createRoute(input: CreateRouteInput!)`: Create a new route
- `updateRoute(id: ID!, input: UpdateRouteInput!)`: Update a route
- `deleteRoute(id: ID!)`: Delete a route
- `createSchedule(input: CreateScheduleInput!)`: Create a new schedule
- `updateSchedule(id: ID!, input: UpdateScheduleInput!)`: Update a schedule
- `deleteSchedule(id: ID!)`: Delete a schedule

## Development
```
npm install
npm run dev
```

## Production
```
npm install
npm start
```

## Environment Variables
- `DB_HOST`: Database host (default: localhost)
- `DB_PORT`: Database port (default: 3308)
- `DB_USER`: Database user (default: root)
- `DB_PASSWORD`: Database password (default: empty)
- `DB_NAME`: Database name (default: transbandung_route)
