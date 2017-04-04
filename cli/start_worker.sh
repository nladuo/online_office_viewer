#!/bin/bash

export QUEUE=default
export REDIS_BACKEND='127.0.0.1:6379'

php resque.php
