#!/bin/sh

set -e

: "${REDIS_INSIGHT_DB_NAME:?Missing DB name}"
: "${REDIS_INSIGHT_DB_HOST:?Missing DB host}"
: "${REDIS_INSIGHT_DB_PORT:?Missing DB port}"

echo "Registering Redis DB in RedisInsight..."
echo "  name: $REDIS_INSIGHT_DB_NAME"
echo "  host: $REDIS_INSIGHT_DB_HOST"
echo "  port: $REDIS_INSIGHT_DB_PORT"

echo "Waiting for RedisInsight to become ready..."

until curl -sf http://redis-insight:5540/ > /dev/null; do
  echo "RedisInsight not ready yet, waiting..."
  sleep 2
done

echo "RedisInsight is up."

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  -X POST http://redis-insight:5540/api/databases \
  -H 'Content-Type: application/json' \
  -d "{
    \"name\": \"$REDIS_INSIGHT_DB_NAME\",
    \"host\": \"$REDIS_INSIGHT_DB_HOST\",
    \"port\": $REDIS_INSIGHT_DB_PORT
  }" || true)

echo "HTTP code = $HTTP_CODE"
echo "Done."
