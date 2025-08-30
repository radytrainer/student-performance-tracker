const WebSocket = require('ws');

class GradeWebSocketServer {
  constructor(port = 6001) {
    this.port = port;
    this.clients = new Map(); // Map to store connected clients with their metadata
    this.server = null;
  }

  start() {
    this.server = new WebSocket.Server({ port: this.port });
    
    console.log(`🚀 WebSocket Server started on port ${this.port}`);
    console.log(`📡 Listening for real-time grade updates...`);

    this.server.on('connection', (ws, req) => {
      const url = new URL(req.url, `http://localhost:${this.port}`);
      const userId = url.searchParams.get('user_id');
      const role = url.searchParams.get('role');
      
      // Store client metadata
      const clientId = `${userId}_${Date.now()}`;
      this.clients.set(ws, {
        id: clientId,
        userId: userId,
        role: role,
        connectedAt: new Date(),
        isAlive: true
      });

      console.log(`✅ New connection: User ${userId} (${role}) - Client ID: ${clientId}`);
      console.log(`📊 Active connections: ${this.clients.size}`);

      // Send welcome message
      ws.send(JSON.stringify({
        type: 'connection_established',
        message: 'Connected to Grade Updates WebSocket',
        clientId: clientId,
        timestamp: new Date().toISOString()
      }));

      // Handle incoming messages
      ws.on('message', (data) => {
        try {
          const message = JSON.parse(data.toString());
          this.handleMessage(ws, message);
        } catch (error) {
          console.error('❌ Error parsing message:', error);
          ws.send(JSON.stringify({
            type: 'error',
            message: 'Invalid message format',
            timestamp: new Date().toISOString()
          }));
        }
      });

      // Handle heartbeat
      ws.on('pong', () => {
        const client = this.clients.get(ws);
        if (client) {
          client.isAlive = true;
        }
      });

      // Handle client disconnect
      ws.on('close', () => {
        const client = this.clients.get(ws);
        if (client) {
          console.log(`❌ Client disconnected: User ${client.userId} (${client.role})`);
          this.clients.delete(ws);
          console.log(`📊 Active connections: ${this.clients.size}`);
        }
      });

      // Handle errors
      ws.on('error', (error) => {
        console.error('🚨 WebSocket error:', error);
        const client = this.clients.get(ws);
        if (client) {
          console.log(`🚨 Error for User ${client.userId}: ${error.message}`);
        }
      });
    });

    // Set up heartbeat interval to check for dead connections
    this.heartbeatInterval = setInterval(() => {
      this.server.clients.forEach((ws) => {
        const client = this.clients.get(ws);
        if (!client) return;

        if (!client.isAlive) {
          console.log(`💀 Terminating dead connection: User ${client.userId}`);
          ws.terminate();
          this.clients.delete(ws);
          return;
        }

        client.isAlive = false;
        ws.ping();
      });
    }, 30000); // Check every 30 seconds

    // Graceful shutdown handling
    process.on('SIGINT', () => {
      console.log('\n🛑 Shutting down WebSocket server...');
      this.shutdown();
    });

    process.on('SIGTERM', () => {
      console.log('\n🛑 Shutting down WebSocket server...');
      this.shutdown();
    });
  }

  handleMessage(ws, message) {
    const client = this.clients.get(ws);
    if (!client) return;

    console.log(`📨 Message from User ${client.userId}:`, message);

    switch (message.type) {
      case 'auth':
        this.handleAuth(ws, message);
        break;
      
      case 'grade_created':
      case 'grade_updated':
      case 'grade_deleted':
        this.broadcastGradeUpdate(ws, message);
        break;
      
      case 'heartbeat_ack':
        console.log(`💓 Heartbeat from User ${client.userId}`);
        break;
      
      case 'ping':
        ws.send(JSON.stringify({
          type: 'pong',
          timestamp: new Date().toISOString()
        }));
        break;
      
      default:
        console.log(`❓ Unknown message type: ${message.type}`);
        ws.send(JSON.stringify({
          type: 'error',
          message: `Unknown message type: ${message.type}`,
          timestamp: new Date().toISOString()
        }));
    }
  }

  handleAuth(ws, message) {
    const client = this.clients.get(ws);
    if (client) {
      client.userId = message.user_id;
      client.role = message.role;
      console.log(`🔐 User authenticated: ${message.user_id} (${message.role})`);
      
      ws.send(JSON.stringify({
        type: 'auth_success',
        message: 'Authentication successful',
        timestamp: new Date().toISOString()
      }));
    }
  }

  broadcastGradeUpdate(senderWs, message) {
    const sender = this.clients.get(senderWs);
    if (!sender) return;

    console.log(`📡 Broadcasting grade update from ${sender.role} ${sender.userId}:`, {
      type: message.type,
      student_id: message.student_id,
      metadata: message.metadata
    });

    // Broadcast to relevant clients
    this.server.clients.forEach((clientWs) => {
      if (clientWs === senderWs) return; // Don't send back to sender
      
      const client = this.clients.get(clientWs);
      if (!client || clientWs.readyState !== WebSocket.OPEN) return;

      // Send to the affected student
      if (client.role === 'student' && client.userId == message.student_id) {
        clientWs.send(JSON.stringify({
          ...message,
          broadcast_to: 'student',
          timestamp: new Date().toISOString()
        }));
        console.log(`📤 Sent to student ${client.userId}: ${message.type}`);
      }
      
      // Send to other teachers (optional - for collaboration)
      if (client.role === 'teacher' && client.userId !== sender.userId) {
        clientWs.send(JSON.stringify({
          ...message,
          broadcast_to: 'teacher',
          timestamp: new Date().toISOString()
        }));
        console.log(`📤 Sent to teacher ${client.userId}: ${message.type}`);
      }
      
      // Send to parents of the affected student (if implemented)
      if (client.role === 'parent' && client.studentId == message.student_id) {
        clientWs.send(JSON.stringify({
          ...message,
          broadcast_to: 'parent',
          timestamp: new Date().toISOString()
        }));
        console.log(`📤 Sent to parent ${client.userId}: ${message.type}`);
      }
    });

    // Send confirmation back to sender
    senderWs.send(JSON.stringify({
      type: 'broadcast_success',
      originalMessage: message.type,
      message: 'Grade update broadcast successfully',
      timestamp: new Date().toISOString()
    }));
  }

  getStats() {
    const stats = {
      totalConnections: this.clients.size,
      students: 0,
      teachers: 0,
      parents: 0,
      unknown: 0
    };

    this.clients.forEach((client) => {
      switch (client.role) {
        case 'student':
          stats.students++;
          break;
        case 'teacher':
          stats.teachers++;
          break;
        case 'parent':
          stats.parents++;
          break;
        default:
          stats.unknown++;
      }
    });

    return stats;
  }

  shutdown() {
    if (this.heartbeatInterval) {
      clearInterval(this.heartbeatInterval);
    }

    console.log('📊 Final connection stats:', this.getStats());

    this.server.clients.forEach((ws) => {
      const client = this.clients.get(ws);
      if (client) {
        ws.send(JSON.stringify({
          type: 'server_shutdown',
          message: 'Server is shutting down',
          timestamp: new Date().toISOString()
        }));
      }
      ws.terminate();
    });

    this.server.close(() => {
      console.log('✅ WebSocket server shut down gracefully');
      process.exit(0);
    });
  }

  // Method to simulate grade updates for testing
  simulateGradeUpdate() {
    const mockUpdate = {
      type: 'grade_created',
      grade: {
        id: Date.now(),
        student_id: '1', // Simulate update for student ID 1
        subject: 'Mathematics',
        assessment_type: 'quiz',
        score_obtained: 85,
        max_score: 100,
        grade_letter: 'B'
      },
      student_id: '1',
      teacher_id: '2',
      timestamp: new Date().toISOString(),
      metadata: {
        subject: 'Mathematics',
        assessment_type: 'quiz',
        grade_letter: 'B',
        score: '85/100'
      }
    };

    console.log('🧪 Simulating grade update...');
    this.server.clients.forEach((ws) => {
      const client = this.clients.get(ws);
      if (client && client.role === 'student' && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify(mockUpdate));
        console.log(`📤 Sent simulated update to student ${client.userId}`);
      }
    });
  }
}

// Start the server
const server = new GradeWebSocketServer(6001);
server.start();

// Log stats every minute
setInterval(() => {
  const stats = server.getStats();
  console.log('📊 Current connections:', stats);
}, 60000);

// For testing - simulate a grade update every 30 seconds if there are connected students
setInterval(() => {
  const stats = server.getStats();
  if (stats.students > 0) {
    server.simulateGradeUpdate();
  }
}, 30000);

module.exports = GradeWebSocketServer;
