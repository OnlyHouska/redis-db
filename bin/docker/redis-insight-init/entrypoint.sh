#!/bin/sh

set -e

: "${REDIS_INSIGHT_DB_NAME:?Missing connection name}"
: "${REDIS_INSIGHT_DB_HOST:?Missing Redis host}"
: "${REDIS_INSIGHT_DB_PORT:?Missing Redis port}"

REDIS_INSIGHT_URL="http://redis-insight:5540"

echo "Registering Redis connection in RedisInsight..."
echo "  connection name: $REDIS_INSIGHT_DB_NAME"
echo "  redis host:      $REDIS_INSIGHT_DB_HOST"
echo "  redis port:      $REDIS_INSIGHT_DB_PORT"

echo "Waiting for RedisInsight to become ready..."

until curl -sf "$REDIS_INSIGHT_URL/" > /dev/null; do
  echo "RedisInsight not ready yet, waiting..."
  sleep 2
done

echo "RedisInsight is up."

echo "Checking if Redis connection already exists (host + port)..."

EXISTING_CONNECTIONS=$(curl -s "$REDIS_INSIGHT_URL/api/databases")

if echo "$EXISTING_CONNECTIONS" | grep -q "\"host\":\"$REDIS_INSIGHT_DB_HOST\".*\"port\":$REDIS_INSIGHT_DB_PORT"; then
  echo "Redis connection for $REDIS_INSIGHT_DB_HOST:$REDIS_INSIGHT_DB_PORT already exists. Skipping creation."
  exit 0
fi

echo "Redis connection not found. Creating new connection..."

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  -X POST "$REDIS_INSIGHT_URL/api/databases" \
  -H 'Content-Type: application/json' \
  -d "{
    \"name\": \"$REDIS_INSIGHT_DB_NAME\",
    \"host\": \"$REDIS_INSIGHT_DB_HOST\",
    \"port\": $REDIS_INSIGHT_DB_PORT
  }" || true)

echo "HTTP response code = $HTTP_CODE"

if [ "$HTTP_CODE" -ge 200 ] && [ "$HTTP_CODE" -lt 300 ]; then
  echo "Redis connection successfully created in RedisInsight."
else
  echo "Redis connection creation failed or already exists (HTTP $HTTP_CODE)."
fi

echo "Done."
