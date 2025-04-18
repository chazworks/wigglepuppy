#!/bin/bash

# Source the docker.sh script
source bin/docker.sh

# Show the logs from the Docker containers
bin/docker.sh logs "$@"
