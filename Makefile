start: app

app: deps
	docker-compose up -d app

bash:
	docker-compose run --rm cli bash

behat:
	docker-compose run --rm cli bash -c "vendor/bin/behat --config=features/behat.yml --stop-on-failure"

behatappend:
	docker-compose run --rm cli bash -c "vendor/bin/behat --config=features/behat.yml --append-snippets"

clean:
	docker-compose kill
	docker system prune -f

deps:
	docker-compose run --rm cli composer install --no-scripts

depsupdate:
	docker-compose run --rm cli composer update --no-scripts

test: deps behat
