#!/bin/sh

# Exit on error
set -e

# Function to display help message
show_help() {
  echo "Available commands:"
  echo "  composer [args]                     - Run Composer commands"
  echo "  php [args]                          - Run PHP commands"
  echo "  [command] [args]                    - Run commands as written"
  echo "  help                                - Show this help message"
  echo ""
  echo "Examples:"
  echo "  docker-compose run --rm app composer install"
  echo "  docker-compose run --rm app php -v"
  echo "  docker-compose run --rm app cache:clear"
}

# If no arguments are provided, show the help message
if [ "$#" -eq 0 ]; then
  show_help
  exit 0
fi

COMMAND="$1"
shift

case "$COMMAND" in
  help)
    show_help
    ;;
  composer)
    # Run composer commands
    exec composer "$@"
    ;;
  php)
    # Run PHP commands
    exec php "$@"
    ;;
  *)
    # For any other command, run 'php bin/console [command] [args]'
    exec "$@"
    ;;
esac
