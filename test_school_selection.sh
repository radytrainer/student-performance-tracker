#!/bin/bash

echo "🧪 Testing School Selection Functionality"
echo "=========================================="

API_BASE="http://localhost:8000/api"

# Test 1: Public schools endpoint
echo "1. 🏫 Testing public schools endpoint..."
curl -s -X GET "$API_BASE/schools" \
    -H "Content-Type: application/json" | jq '.' 2>/dev/null || echo "Public schools response received"

echo ""

# Test 2: Login as super admin
echo "2. 🔐 Testing super admin login..."
login_response=$(curl -s -X POST "$API_BASE/auth/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"admin@school.com","password":"admin123"}')

echo "$login_response" | jq '.' 2>/dev/null || echo "$login_response"

# Extract token
if command -v jq >/dev/null 2>&1; then
    TOKEN=$(echo "$login_response" | jq -r '.token // .access_token // empty')
    IS_SUPER_ADMIN=$(echo "$login_response" | jq -r '.user.is_super_admin // false')
    
    if [ "$IS_SUPER_ADMIN" = "true" ]; then
        echo "✅ Super admin login successful"
    else
        echo "❌ User is not super admin"
    fi
else
    echo "⚠️  jq not found. Please install jq or extract token manually"
    read -p "Enter the token manually: " TOKEN
fi

echo ""

if [ -z "$TOKEN" ] || [ "$TOKEN" = "null" ]; then
    echo "❌ No token received. Cannot continue with user creation test."
    exit 1
fi

echo "🎯 Token: ${TOKEN:0:20}..."
echo ""

# Test 3: Create user with school assignment
echo "3. 👤 Testing user creation with school selection..."
user_data='{
    "username": "test_school_user",
    "email": "test.school@example.com",
    "password": "password123",
    "first_name": "Test",
    "last_name": "User",
    "role": "student",
    "school_id": 1
}'

create_user_response=$(curl -s -X POST "$API_BASE/users" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d "$user_data")

echo "$create_user_response" | jq '.' 2>/dev/null || echo "$create_user_response"

echo ""

# Test 4: Verify user was created with correct school
echo "4. 👀 Verifying user creation with school assignment..."
if command -v jq >/dev/null 2>&1; then
    USER_ID=$(echo "$create_user_response" | jq -r '.id // .user.id // empty')
    
    if [ -n "$USER_ID" ] && [ "$USER_ID" != "null" ]; then
        echo "User created with ID: $USER_ID"
        
        # Get user details
        user_details=$(curl -s -X GET "$API_BASE/users/$USER_ID" \
            -H "Authorization: Bearer $TOKEN" \
            -H "Content-Type: application/json")
        
        echo "User details:"
        echo "$user_details" | jq '.' 2>/dev/null || echo "$user_details"
        
        # Cleanup: Delete test user
        echo ""
        echo "5. 🧹 Cleaning up test user..."
        curl -s -X DELETE "$API_BASE/users/$USER_ID" \
            -H "Authorization: Bearer $TOKEN" \
            -H "Content-Type: application/json" | jq '.' 2>/dev/null || echo "Cleanup completed"
    else
        echo "❌ User creation failed or user ID not found"
    fi
else
    echo "⚠️  jq not available for detailed verification"
fi

echo ""
echo "🎉 School Selection Testing Complete!"
echo ""
echo "📋 What to check:"
echo "✅ Public schools endpoint returns school list"
echo "✅ Super admin can create users with school assignment"
echo "✅ Created user has correct school_id"
echo "✅ Frontend should show school dropdown for super admin"
