#!/bin/bash

# Source the docker.sh script
source bin/docker.sh

# Stop the Docker containers, remove all images, volumes, and orphaned containers
bin/docker.sh down --rmi all -v --remove-orphans
