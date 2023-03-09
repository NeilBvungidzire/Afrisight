up:
	@docker-compose -f docker-compose.dev.yml up -d nginx mariadb redis php-worker postgres

restart:
	@docker-compose restart

stop:
	@docker-compose stop

down:
	@docker-compose down -v

exec:
	@docker-compose exec --user=laradock workspace bash
