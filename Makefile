run:
	php bin/console cache:clear
	php bin/console doctrine:migrations:migrate
	php bin/console app:bot

DOCKER_HOST=$(shell cat .deploy_host)
deploy:
	echo "Deploying to ${DOCKER_HOST}"
	DOCKER_HOST=${DOCKER_HOST} docker-compose up -d --build