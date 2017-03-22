
clean:
	docker-compose kill
	docker system prune -f

behat:
	docker-compose run --rm cli bash -c "vendor/bin/behat"

behatappend:
	docker-compose run --rm cli bash -c "vendor/bin/behat --append-snippets"

deps:
	docker-compose run --rm cli composer install

depsupdate:
	docker-compose run --rm cli composer update

test: deps behat
