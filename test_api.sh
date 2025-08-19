#!/bin/bash

# API Testing Script for Hierarchical Admin System
# Make sure backend is running: php artisan serve

API_BASE="http://localhost:8000/api"
SUPER_ADMIN_EMAIL="admin@school.com"
SUPER_ADMIN_PASSWORD="admin123"

echo "🧪 Testing API Endpoints"
echo "========================="

# Function to make API calls with better formatting
api_call() {
    local method=$1
    local endpoint=$2
    local token=$3
    local data=$4
    
    echo "🔗 $method $endpoint"
    
    if [ -n "$data" ]; then
        if [ -n "$token" ]; then
            curl -s -X $method "$API_BASE$endpoint" \
                -H "Authorization: Bearer $token" \
                -H "Content-Type: application/json" \
                -d "$data" | jq '.' 2>/dev/null || echo "Response received (jq not available)"
        else
            curl -s -X $method "$API_BASE$endpoint" \
                -H "Content-Type: application/json" \
                -d "$data" | jq '.' 2>/dev/null || echo "Response received (jq not available)"
        fi
    else
        if [ -n "$token" ]; then
            curl -s -X $method "$API_BASE$endpoint" \
                -H "Authorization: Bearer $token" \
                -H "Content-Type: application/json" | jq '.' 2>/dev/null || echo "Response received (jq not available)"
        else
            curl -s -X $method "$API_BASE$endpoint" \
                -H "Content-Type: application/json" | jq '.' 2>/dev/null || echo "Response received (jq not available)"
        fi
    fi
    echo ""
}

# Test 1: Login as Super Admin
echo "1. 🔐 Testing Super Admin Login"
login_response=$(curl -s -X POST "$API_BASE/login" \
    -H "Content-Type: application/json" \
    -d "{\"email\":\"$SUPER_ADMIN_EMAIL\",\"password\":\"$SUPER_ADMIN_PASSWORD\"}")

echo "$login_response" | jq '.' 2>/dev/null || echo "$login_response"

# Extract token (requires jq)
if command -v jq >/dev/null 2>&1; then
    SUPER_ADMIN_TOKEN=$(echo "$login_response" | jq -r '.token // .access_token // empty')
    IS_SUPER_ADMIN=$(echo "$login_response" | jq -r '.user.is_super_admin // false')
    
    if [ "$IS_SUPER_ADMIN" = "true" ]; then
        echo "✅ Super admin login successful"
    else
        echo "❌ User is not super admin"
    fi
else
    echo "⚠️  jq not found. Please install jq for better JSON parsing or extract token manually"
    echo "Example token extraction line:"
    echo "$login_response"
    echo ""
    read -p "Enter the token manually: " SUPER_ADMIN_TOKEN
fi

echo ""

if [ -z "$SUPER_ADMIN_TOKEN" ] || [ "$SUPER_ADMIN_TOKEN" = "null" ]; then
    echo "❌ No token received. Cannot continue with API tests."
    echo "Make sure:"
    echo "1. Backend is running (php artisan serve)"
    echo "2. Super admin is configured (php artisan app:set-super-admin admin@school.com)"
    exit 1
fi

echo "🎯 Token: ${SUPER_ADMIN_TOKEN:0:20}..."
echo ""

# Test 2: Get Super Admin Stats
echo "2. 📊 Testing Super Admin Stats"
api_call "GET" "/super-admin/stats" "$SUPER_ADMIN_TOKEN"

# Test 3: Get Schools List
echo "3. 🏫 Testing Schools List"
api_call "GET" "/super-admin/schools" "$SUPER_ADMIN_TOKEN"

# Test 4: Create a Test School
echo "4. ➕ Creating Test School"
school_data='{
    "name": "API Test School",
    "code": "APITEST001",
    "email": "api@testschool.edu",
    "status": "active"
}'
create_school_response=$(curl -s -X POST "$API_BASE/super-admin/schools" \
    -H "Authorization: Bearer $SUPER_ADMIN_TOKEN" \
    -H "Content-Type: application/json" \
    -d "$school_data")

echo "$create_school_response" | jq '.' 2>/dev/null || echo "$create_school_response"

# Extract school ID
if command -v jq >/dev/null 2>&1; then
    SCHOOL_ID=$(echo "$create_school_response" | jq -r '.data.id // empty')
    echo "Created school ID: $SCHOOL_ID"
else
    echo "⚠️  Extract school ID manually from response above"
    read -p "Enter the school ID: " SCHOOL_ID
fi

echo ""

if [ -n "$SCHOOL_ID" ] && [ "$SCHOOL_ID" != "null" ]; then
    # Test 5: Create Sub-Admin
    echo "5. 👤 Creating Sub-Admin for School $SCHOOL_ID"
    subadmin_data='{
        "username": "apitest_subadmin",
        "email": "subadmin@apitest.edu",
        "password": "password123",
        "first_name": "API",
        "last_name": "Subadmin"
    }'
    api_call "POST" "/super-admin/schools/$SCHOOL_ID/sub-admins" "$SUPER_ADMIN_TOKEN" "$subadmin_data"

    # Test 6: Get Sub-Admins
    echo "6. 👥 Getting Sub-Admins for School $SCHOOL_ID"
    api_call "GET" "/super-admin/schools/$SCHOOL_ID/sub-admins" "$SUPER_ADMIN_TOKEN"

    # Test 7: Test Sub-Admin Login
    echo "7. 🔐 Testing Sub-Admin Login"
    subadmin_login_response=$(curl -s -X POST "$API_BASE/login" \
        -H "Content-Type: application/json" \
        -d '{"email":"subadmin@apitest.edu","password":"password123"}')
    
    echo "$subadmin_login_response" | jq '.' 2>/dev/null || echo "$subadmin_login_response"
    
    if command -v jq >/dev/null 2>&1; then
        SUB_ADMIN_TOKEN=$(echo "$subadmin_login_response" | jq -r '.token // .access_token // empty')
        SUB_ADMIN_SCHOOL_ID=$(echo "$subadmin_login_response" | jq -r '.user.school_id // empty')
        echo "Sub-admin school ID: $SUB_ADMIN_SCHOOL_ID"
    fi

    # Test 8: Test Data Isolation
    echo ""
    echo "8. 🔒 Testing Data Isolation"
    
    if [ -n "$SUB_ADMIN_TOKEN" ] && [ "$SUB_ADMIN_TOKEN" != "null" ]; then
        echo "Sub-admin trying to access super-admin routes (should fail):"
        api_call "GET" "/super-admin/schools" "$SUB_ADMIN_TOKEN"
        
        echo "Sub-admin accessing regular users endpoint (should work):"
        api_call "GET" "/users" "$SUB_ADMIN_TOKEN"
    else
        echo "⚠️  Sub-admin token not available for isolation testing"
    fi

    # Cleanup: Delete test school
    echo ""
    echo "9. 🧹 Cleaning up - Deleting test school"
    api_call "DELETE" "/super-admin/schools/$SCHOOL_ID" "$SUPER_ADMIN_TOKEN"
fi

echo ""
echo "🎉 API Testing Complete!"
echo ""
echo "📋 What to check:"
echo "✅ Super admin login should return is_super_admin: true"
echo "✅ Super admin should see stats for all schools"
echo "✅ Super admin should be able to create schools"
echo "✅ Super admin should be able to create sub-admins"
echo "✅ Sub-admins should NOT be able to access super-admin routes"
echo "✅ Sub-admins should only see data from their own school"
