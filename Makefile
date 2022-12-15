.PHONY: docker_start docker_stop

this_container := $(shell pwd)
api_db_container := ../tp-mysql
solr_container := ../tp-solr
api_container_old := ../../gitlab/tp_api_gitlab
api_container := ../tp-api

docker_start:
	@echo "Starting API database container..."
	cd $(api_db_container) && sudo docker-compose up -d
	@echo "Starting Java API container..."
	cd $(api_container_old) && sudo docker-compose up -d
	@echo "Starting SOLR container..."
	cd $(solr_container) && sudo docker-compose up -d
	@echo "Starting Laravel API container..."
	cd $(api_container) && sudo docker-compose up -d
	@echo "Starting PHP/Apache container..."
	cd $(this_container) && sudo docker-compose up -d
	@echo
	@echo "----"
	@echo "Java API host is available on tomcat9:8080"
	@echo "SOLR is available on tp_solr:8983"
	@echo "API database is running on tp_mysql:3306"
	@echo "Platfrom database is running on tp_wordpress_db:3306"
	@echo "Webserver running on https://localhost/ and https://transcribathon.eu.local/"
	@echo "Mailhog running on http://localhost:8025"
	@echo "----"
	@echo "I'm up to no good..."
	@echo

docker_stop:
	@echo
	@echo "----"
	@echo "Stopping all containers..."
	cd $(api_db_container) && sudo docker-compose down
	cd $(api_container_old) && sudo docker-compose down
	cd $(solr_container) && sudo docker-compose down
	cd $(api_container) && sudo docker-compose down
	cd $(this_container) && sudo docker-compose down
	@echo "----"
	@echo "...mischief managed."
	@echo

deploy_local:
	@echo
	@echo "----"
	@echo "Delpoying to .local..."
	@bash deploy.sh local
	@echo "----"
	@echo "...deploy done"
	@echo

deploy_dev:
	@echo
	@echo "----"
	@echo "Delpoying to DEV..."
	@bash deploy.sh dev
	@echo "----"
	@echo "...deploy done"
	@echo

deploy_live:
	@echo
	@echo "----"
	@echo "Delpoying to LIVE..."
	@bash deploy.sh live
	@echo "----"
	@echo "...deploy done"
	@echo

