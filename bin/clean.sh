#!/bin/bash

# Source the docker.sh script
source bin/docker.sh

# Stop the Docker containers and remove volumes and orphaned containers
bin/docker.sh down -v --remove-orphans
