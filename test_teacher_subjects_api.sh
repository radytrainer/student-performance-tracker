#!/bin/bash

# Teacher-Subject Assignment API Tests
BASE_URL="http://localhost:8000/api"

echo "=== Teacher-Subject Assignment API Tests ==="
echo

# First, let's login as admin to get a token
echo "1. Logging in as admin..."
LOGIN_RESPONSE=$(curl -s -X POST "${BASE_URL}/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@school.com", "password": "admin123"}')

TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "Failed to get auth token"
    echo "Login response: $LOGIN_RESPONSE"
    exit 1
fi

echo "Login successful!"
echo

# Test 2: Get a teacher's subjects
TEACHER_ID=2
echo "2. Getting subjects for teacher ID $TEACHER_ID..."
curl -s -X GET "${BASE_URL}/admin/teachers/${TEACHER_ID}/subjects" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | python -m json.tool

echo
echo

# Test 3: Get teachers for a subject
SUBJECT_ID=1
echo "3. Getting teachers for subject ID $SUBJECT_ID..."
curl -s -X GET "${BASE_URL}/admin/subjects/${SUBJECT_ID}/teachers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | python -m json.tool

echo
echo

# Test 4: Assign new subjects to a teacher
echo "4. Assigning subjects to teacher..."
curl -s -X POST "${BASE_URL}/admin/teachers/${TEACHER_ID}/subjects" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"subject_ids": [3, 4], "primary_subject_id": 3}' | python -m json.tool

echo
echo

# Test 5: Bulk assign subjects to multiple teachers
echo "5. Bulk assigning subjects to teachers..."
curl -s -X POST "${BASE_URL}/admin/teachers/bulk-assign-subjects" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "assignments": [
      {
        "teacher_id": 3,
        "subject_ids": [5, 6],
        "primary_subject_id": 5
      },
      {
        "teacher_id": 4,
        "subject_ids": [7, 8],
        "primary_subject_id": 7
      }
    ]
  }' | python -m json.tool

echo
echo

echo "=== API Tests Complete ==="
