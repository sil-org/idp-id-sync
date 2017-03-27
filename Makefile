start: app

app: db deps tables basemodels
	docker-compose up -d app

basemodels: db tables
	docker-compose run --rm cli whenavail db 3306 100 ./rebuildbasemodels.sh

behat:
	docker-compose run --rm cli bash -c "vendor/bin/behat --config=features/behat.yml --stop-on-failure"

behatappend:
	docker-compose run --rm cli bash -c "vendor/bin/behat --config=features/behat.yml --append-snippets"

clean:
	docker-compose kill
	docker system prune -f

db:
	docker-compose up -d db

deps:
	docker-compose run --rm cli composer install --no-scripts

depsupdate:
	docker-compose run --rm cli composer update --no-scripts

tables: db
	docker-compose run --rm cli whenavail db 3306 100 ./yii migrate --interactive=0

test: deps behat
