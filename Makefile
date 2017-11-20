start: broker app

app: deps
	docker-compose up -d app

bash:
	docker-compose run --rm cli bash

behat:
	docker-compose run --rm cli bash -c "whenavail brokerdb 3306 20 vendor/bin/behat --config=features/behat.yml --strict --stop-on-failure"

behatlocal:
	docker-compose run --rm cli bash -c "whenavail brokerdb 3306 20 vendor/bin/behat --config=features/behat.yml --strict --stop-on-failure --tags '~@integration'"

behatlocalappend:
	docker-compose run --rm cli bash -c "whenavail brokerdb 3306 20 vendor/bin/behat --config=features/behat.yml --tags '~@integration' --append-snippets"

behatv:
	docker-compose run --rm cli bash -c "whenavail brokerdb 3306 20 vendor/bin/behat -v --config=features/behat.yml --strict --stop-on-failure"

behatappend:
	docker-compose run --rm cli bash -c "whenavail brokerdb 3306 20 vendor/bin/behat --config=features/behat.yml --append-snippets"

broker:
	docker-compose up -d brokerdb brokercron broker

clean:
	docker-compose kill
	docker system prune -f

deps:
	docker-compose run --rm cli composer install --no-scripts

depsupdate:
	docker-compose run --rm cli composer update --no-scripts

# NOTE: When running tests locally, make sure you don't exclude the integration
#       tests (which we do when testing on Codeship).
test: deps app broker
	sleep 11 && make behat

testci: deps app broker
	docker-compose run --rm cli bash -c "./run-tests.sh"
