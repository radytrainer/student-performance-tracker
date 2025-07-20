// Mock API for testing authentication without backend
// Replace the real API calls temporarily for testing

const mockUsers = [
  {
    id: 1,
    username: 'admin',
    email: 'admin@school.com',
    password: 'admin123',
    role: 'admin',
    first_name: 'Admin',
    last_name: 'User'
  },
  {
    id: 2,
    username: 'teacher1',
    email: 'teacher@school.com', 
    password: 'teacher123',
    role: 'teacher',
    first_name: 'John',
    last_name: 'Teacher'
  },
  {
    id: 3,
    username: 'student1',
    email: 'student@school.com',
    password: 'student123', 
    role: 'student',
    first_name: 'Jane',
    last_name: 'Student'
  }
];

let currentUser = null;

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

export default {
  async login(credentials) {
    await delay(1000); // Simulate API delay
    
    const user = mockUsers.find(u => 
      u.email === credentials.email && u.password === credentials.password
    );
    
    if (!user) {
      throw new Error('Invalid email or password');
    }
    
    currentUser = user;
    localStorage.setItem('mockUser', JSON.stringify(user));
    
    return {
      data: {
        user: {
          id: user.id,
          username: user.username,
          email: user.email,
          role: user.role,
          first_name: user.first_name,
          last_name: user.last_name
        },
        token: 'mock-jwt-token'
      }
    };
  },

  async register(userData) {
    await delay(1000); // Simulate API delay
    
    // Check if email already exists
    const existingUser = mockUsers.find(u => u.email === userData.email);
    if (existingUser) {
      throw new Error('Email already registered');
    }
    
    // Check if username already exists
    const existingUsername = mockUsers.find(u => u.username === userData.username);
    if (existingUsername) {
      throw new Error('Username already taken');
    }
    
    // Create new user
    const newUser = {
      id: mockUsers.length + 1,
      username: userData.username,
      email: userData.email,
      password: userData.password,
      role: userData.role,
      first_name: userData.first_name,
      last_name: userData.last_name
    };
    
    mockUsers.push(newUser);
    currentUser = newUser;
    localStorage.setItem('mockUser', JSON.stringify(newUser));
    
    return {
      data: {
        user: {
          id: newUser.id,
          username: newUser.username,
          email: newUser.email,
          role: newUser.role,
          first_name: newUser.first_name,
          last_name: newUser.last_name
        },
        token: 'mock-jwt-token'
      }
    };
  },

  async logout() {
    await delay(500);
    currentUser = null;
    localStorage.removeItem('mockUser');
    return { data: { message: 'Logged out successfully' } };
  },

  async getUser() {
    await delay(500);
    
    const storedUser = localStorage.getItem('mockUser');
    if (storedUser) {
      currentUser = JSON.parse(storedUser);
      return {
        data: {
          id: currentUser.id,
          username: currentUser.username,
          email: currentUser.email,
          role: currentUser.role,
          first_name: currentUser.first_name,
          last_name: currentUser.last_name
        }
      };
    }
    
    throw new Error('User not authenticated');
  }
};
