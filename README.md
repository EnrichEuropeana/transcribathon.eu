# transcribathon.eu

Dockerized worpdress instance of www.transcribathon.eu for development.

## prerequisites

We need to install git, docker and docker-compose (IIRC on windows it's named docker desktop)

## building the local environment

Clone the repository first and switch to the newly created project folder.

    $ git clone git@github.com:Facts-and-Files/transcribathon.eu.git
    $ cd transcribathon.eu

First time we need to build the three containers (MySQL, PHP/Apache, Mailhog) we will be using. For the used variables in wp-config modify the `.env.example` to your needs and rename it to `.env`.

    $ mv .env.example .env

Then build the containers:

    $ sudo docker-compose build

### additional choires

We do not track the MySQL database and the ``uploads`` folder, because of the size and the changes from users in the live system.
We just need to import a prepared MySQL dump for the local development and download the ``uploads`` folder from the live server.

The MySQL dump can be imported with the provided adminer.php (a small database manager) after startting the containers. The adminer.php is then located at https://127.0.0.1/adminer.php.

## starting/stoping

Then we just need to start the containers.

    $ sudo docker-compose up

We can stop these with CTRL-C.

But we can also use a detached service that will run in the background:

    $ sudo docker-compose up -d

The detached service can stopped with:

    $ sudo docker-compose down

## Additional API containers

If we want to start all containers for the Transcribathon Platform we need the following containers:

* https://github.com/Facts-and-Files/tp-mysql
* https://gitlab.com/transcribathon/tp_api

Set these up come back and start all with (GNU Make required)

    $ make docker_start

and stop all of them with

    $ make docker_stop

## considerations

By default the php container will serve the website via https://transcribathon.eu.local and https://europena.transcribathon.eu.local. So change your hosts file to access it on localhost/127.0.0.1.
Mails in the conainer are not working directly when not set up with SMTP. So all mails from the Wordpress get catched by Mailhog. The mails in Mailhog can be viewed via http://127.0.0.1:8025.
