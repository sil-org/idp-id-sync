sync_full: broker deps
	docker compose run --rm app

sync_incremental: broker deps
	docker compose run --rm app bash -c "/data/yii batch/incremental"

bash:
	docker compose run --rm cli bash

behat:
	docker compose run --rm cli bash -c "whenavail brokerdb 3306 20 vendor/bin/behat --config=features/behat.yml --strict --stop-on-failure"

behatlocal:
	docker compose run --rm cli bash -c "whenavail brokerdb 3306 20 vendor/bin/behat --config=features/behat.yml --strict --stop-on-failure --tags '~@integration'"

behatlocalappend:
	docker compose run --rm cli bash -c "whenavail brokerdb 3306 20 vendor/bin/behat --config=features/behat.yml --tags '~@integration' --append-snippets"

behatv:
	docker compose run --rm cli bash -c "whenavail brokerdb 3306 20 vendor/bin/behat -v --config=features/behat.yml --strict --stop-on-failure"

behatappend:
	docker compose run --rm cli bash -c "whenavail brokerdb 3306 20 vendor/bin/behat --config=features/behat.yml --append-snippets"

broker:
	docker compose up -d brokerdb brokercron broker
	make wait_for_broker

clean:
	docker compose kill
	docker compose rm -f

deps:
	docker compose run --rm cli composer install --no-scripts

composershow:
	docker compose run --rm cli bash -c 'composer show --format=json --no-dev --no-ansi --locked | jq "[.locked[] | { \"name\": .name, \"version\": .version }]" > dependencies.json'

depsupdate:
	docker compose run --rm cli bash -c "composer update --no-scripts"
	make composershow

phpmyadmin:
	docker compose up -d phpmyadmin

psr2:
	docker compose run --rm cli bash -c "vendor/bin/php-cs-fixer fix ."

# NOTE: When running tests locally, make sure you don't exclude the integration
#       tests (which we do when testing on Codeship).
test: deps unittest broker behat

testci: deps broker
	docker compose run --rm cli bash -c "./run-tests.sh"

unittest:
	docker compose run --rm cli vendor/bin/phpunit

wait_for_broker:
	docker compose run --rm cli whenavail broker 80 20 echo "Broker is ready"
