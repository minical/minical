# Using Docker to run a Minical demo server

You need to have [docker and docker-compose](https://docs.docker.com/desktop/) installed on your system.

Once installed, you can simply run:

```
$ cd docker
docker/ $ docker-compose up
```

This would download and build all docker images and will run all services.

You still need to create the `.env` file in the main directory as described in the Minical Installation
instructions. You can copy the `.env` file from the `docker` directory into Minical's root.

NOTE that it is best to change the URLs in the `.env` file from `localhost` to your actual IP address.
This is necessary to get some functions working, such as the Room Inventory page which makes internal
calls to the site using the API interface.

For running the demo server using docker, you can simply run the following command in the
main Minical directory:

```
$ cp docker/.env.example ./.env
```

or if you are in the docker directory:

```
docker/ $ cp .env.example ../.env
```

When everything is started, and the `.env` file exists in the Minical root directory, you can simply
visit http://localhost:8080/public/install/index.php to continue with the Minical installation and setup.
