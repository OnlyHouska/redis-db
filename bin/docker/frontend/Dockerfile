FROM node:24

# Set the /app as a current working directory
# All subsequent commands:
# - RUN
# - COPY (its destination path, if not absolute)
# - CMD
# shall execute inside of this docker directory
WORKDIR /app

# Copy the project root (all its files) into the /app directory of docker container
# Beware, that ../../ must be within docker's context
COPY ../../ /app

# Install
# - git and unzip for pulling composer packages
RUN apt-get update && apt-get install -y \
    git \
    unzip

RUN rm -rf /var/lib/apt/lists/*

RUN git config --global --add safe.directory /app

CMD ["sh", "-c", "npm install && npm run dev"]
