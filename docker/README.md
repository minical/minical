# Using Docker to run a Minical demo server

You need to have [docker and docker-compose](https://docs.docker.com/desktop/) installed on your system.

Once installed, you can simply run:

```
$ cd docker
docker/ $ docker-compose up
```

This would download and build all docker images and will run all services.

You still need to create the `.env` file in the main directory as described in the Minical Installation
instructions. For running the demo server using docker, you can simply run the following command in the
main Minical directory:

```
$ cp docker/.env ./.env
```

or if you are in the docker directory:

```
docker/ $ cp .env ../.env
```

When everything is started, and the `.env` file exists in the Minical root directory, you can simply
visit http://localhost:8080/public/install.php to continue with the Minical installation and setup.
