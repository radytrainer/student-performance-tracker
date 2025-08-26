# Real-Time Grade Updates System

This guide explains how to set up and use the real-time grade updates system that allows students to see grade changes immediately when teachers submit them.

## 🚀 Features Implemented

### For Students:
- **Live Grade Updates**: Grades appear instantly when teachers submit them
- **Connection Status Indicator**: Shows when real-time updates are active
- **Update Notifications**: Visual notifications for new/updated/removed grades
- **Performance Metrics**: Real-time recalculation of GPA and performance summaries
- **Browser Notifications**: Desktop notifications when new grades are received

### For Teachers:
- **Instant Broadcasting**: Grade submissions are immediately sent to affected students
- **Connection Monitoring**: WebSocket connection status monitoring
- **Success Confirmations**: Feedback when updates are successfully broadcast

## 🛠️ Technical Implementation

### Frontend Components:

1. **WebSocket Service** (`frontend/src/services/websocketService.js`)
   - Manages WebSocket connections
   - Handles reconnection logic
   - Provides subscription-based event handling

2. **WebSocket Composables** (`frontend/src/composables/useWebSocket.js`)
   - Vue 3 composables for easy WebSocket integration
   - Grade-specific update handlers
   - Notification management

3. **Updated Views**:
   - `StudentGrades.vue`: Real-time grade reception and display
   - `TeacherGrades.vue`: Real-time grade broadcasting

### Backend Integration:
- WebSocket server for real-time communication
- Grade submission broadcasting
- User authentication and role-based access

## 📋 Setup Instructions

### 1. Install WebSocket Server Dependencies

```bash
# Install WebSocket server dependencies
npm install ws nodemon

# Or if you prefer using the package file:
cp package-websocket.json package.json
npm install
```

### 2. Start the WebSocket Server

```bash
# Start the WebSocket server
node websocket-server.js

# Or for development with auto-restart
npm run dev
```

The server will start on port 6001 by default.

### 3. Configure Frontend

The frontend is already configured to connect to the WebSocket server. Make sure your backend API is running and the WebSocket server is accessible.

### 4. Test the System

1. **Open Student View**: Navigate to the student grades page
2. **Open Teacher View**: In another browser tab/window, open the teacher grades page
3. **Add a Grade**: As a teacher, submit a new grade for a student
4. **Observe Real-time Update**: The student's grade view should immediately show the new grade

## 🔧 Configuration

### WebSocket Connection Settings

In `frontend/src/services/websocketService.js`:

```javascript
getWebSocketUrl() {
  // For development
  if (host.includes('localhost')) {
    return `ws://localhost:6001/app/your-app-key`
  }
  // For production
  return `${protocol}//${host}/ws`
}
```

### Connection Parameters

- **Port**: 6001 (configurable in `websocket-server.js`)
- **Reconnection**: Automatic with exponential backoff
- **Heartbeat**: 30-second intervals to detect dead connections

## 📱 User Experience

### Student Experience:
1. **Connection Status**: Green dot indicates live updates are active
2. **New Grade Notifications**: Blue banner appears when grades are added/updated
3. **Browser Notifications**: Desktop notifications (requires permission)
4. **Real-time Calculations**: GPA and performance metrics update instantly
5. **Update Timestamp**: Shows when the last grade update was received

### Teacher Experience:
1. **Submission Feedback**: Success notifications after grade submissions
2. **Broadcasting Confirmation**: Console logs confirm real-time broadcasts
3. **Connection Monitoring**: WebSocket connection status in browser console

## 🧪 Testing

### Manual Testing:
1. Open two browser windows/tabs
2. Login as student in one, teacher in another
3. Submit a grade as teacher
4. Verify instant update in student view

### Automated Testing:
The WebSocket server includes simulation features:
- Automatic grade update simulation every 30 seconds
- Connection statistics logging
- Health check capabilities

### Debug Mode:
Enable console logging to see WebSocket events:
```javascript
console.log('📨 WebSocket message received:', data)
console.log('✅ Real-time grade update broadcast:', updateMessage)
```

## 🔍 Troubleshooting

### Common Issues:

1. **WebSocket Connection Failed**
   - Check if the WebSocket server is running on port 6001
   - Verify firewall settings
   - Ensure correct WebSocket URL configuration

2. **No Real-time Updates**
   - Check browser console for WebSocket errors
   - Verify user authentication
   - Check if grades are being submitted correctly

3. **Connection Drops**
   - The system automatically attempts to reconnect
   - Check network stability
   - Monitor server logs for connection issues

### Server Logs:
```
🚀 WebSocket Server started on port 6001
✅ New connection: User 1 (student) - Client ID: 1_1703234567890
📨 Message from User 2: grade_created
📡 Broadcasting grade update from teacher 2
📤 Sent to student 1: grade_created
```

### Client Logs:
```
🔌 WebSocket connected for teacher grade updates
✅ Real-time grade update broadcast: {type: 'grade_created', ...}
🔄 Handling grade update: {type: 'grade_created', ...}
📊 Recalculating performance metrics...
```

## 🔒 Security Considerations

1. **Authentication**: Users must be authenticated before WebSocket connection
2. **Role-based Access**: Students only receive their own grade updates
3. **Data Validation**: All messages are validated before processing
4. **Connection Limits**: Server monitors and limits connections per user

## 🚀 Production Deployment

For production deployment:

1. **Use WSS (Secure WebSocket)**:
   ```javascript
   const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:'
   ```

2. **Load Balancing**: Consider using Redis for scaling across multiple servers

3. **Monitoring**: Implement proper logging and monitoring

4. **SSL/TLS**: Ensure secure connections in production

## 📈 Performance Metrics

The system tracks:
- Active connections by user type
- Message broadcast success rates
- Connection uptime and stability
- Real-time update delivery times

## 🤝 Contributing

To extend the real-time system:

1. Add new event types in `websocketService.js`
2. Create new composables in `useWebSocket.js`
3. Update UI components to handle new event types
4. Add corresponding server-side handlers

## 📞 Support

For issues or questions:
1. Check the browser console for WebSocket errors
2. Review server logs for connection issues
3. Verify API endpoints are working correctly
4. Test with the simulation features for debugging

---

**Note**: This implementation provides a foundation for real-time grade updates. For production use, consider implementing additional features like message queuing, persistent connections, and advanced error handling.
