#!/bin/bash

echo "🧪 Testing Classes API"
echo "====================="

# First login to get token
echo "1. Getting auth token..."
login_response=$(curl -s -X POST "http://localhost:8000/api/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"admin@school.com","password":"admin123"}')

echo "$login_response"

# Extract token (if jq is available)
if command -v jq >/dev/null 2>&1; then
    TOKEN=$(echo "$login_response" | jq -r '.token // .access_token // empty')
    echo "Token: ${TOKEN:0:20}..."
else
    echo "Please extract token manually and run:"
    echo "curl -H 'Authorization: Bearer YOUR_TOKEN' http://localhost:8000/api/admin/classes"
    exit 1
fi

echo ""
echo "2. Testing classes endpoint..."
curl -s -X GET "http://localhost:8000/api/admin/classes" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" | jq '.' 2>/dev/null || echo "Classes API response received"

echo ""
echo "3. Testing simple classes endpoint..."
curl -s -X GET "http://localhost:8000/api/classes" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" | jq '.' 2>/dev/null || echo "Simple classes API response received"
