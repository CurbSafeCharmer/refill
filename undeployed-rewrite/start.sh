#!/bin/bash

make web & web=$!
make celery & celery=$!
make flask & flask=$!

function interrupt {
	echo Exiting
	kill $web
	kill $celery
	kill $flask
}
trap interrupt HUP INT TERM

wait $web && wait $celery && wait $flask
