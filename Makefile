DOCKER_COMPOSE = docker compose

.PHONY: build start down composer mkdir

build:
	$(DOCKER_COMPOSE) build

start:
	$(DOCKER_COMPOSE) up -d
	$(MAKE) composer

down:
	$(DOCKER_COMPOSE) down

composer:
	@echo "Doing Composer"
	$(DOCKER_COMPOSE) exec php composer install --optimize-autoloader --profile

mysql-create:
	@$(DOCKER_COMPOSE) exec mysql mysqladmin --default-character-set=utf8mb4 -u root -proot create inbox-local
	@$(DOCKER_COMPOSE) exec mysql sh -c "mysql -u root -proot inbox-local < /tmp/mysql/files/inbox.schema.sql"