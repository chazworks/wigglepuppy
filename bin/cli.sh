#!/bin/bash

# Source the docker.sh script
source bin/docker.sh

# Run WP-CLI commands in the Docker environment
bin/docker.sh run --rm wp-cli "$@"
