# Taskana

Simple Task management for small teams with the ability to delegate tasks to users and write comments.

Developed using Symfony 6 PHP framework, Vue 3 and Vuetify 3.

API documentation is available in OpenAPI 3.0 format under `/api/doc` path.

Contents:

- [Demo](#demo)
- [Stack](#stack)
- [Future plans](#future-plans)
- [Previews](#previews)
- [Docker local setup](#docker-local-setup)
- [Security](#security)

## Demo

Demo is available at the address: **https://taskana.fkv.pl**

Log in using:

* Username: **taskana@fkv.pl** or **user@fkv.pl**
* Password: **password**

## Stack

* Symfony 6.1
* PHP 8.1
* Doctrine DBAL 3
* MySQL 8
* **Vue 3** / Composition API
* **Vuetify 3** / Material Design

Analyzed with PHPStan on level 9.

## Future plans

* Add user roles and granular permissions to limit access to only content they own
* Notifications about assigned tasks and new comments
* User registration
* Data log (for admin user)

## Previews

**Task details:**

![task-details](https://user-images.githubusercontent.com/8025853/204584313-bd570d06-5b9b-4fe2-8c49-6694cc2d331c.png)

**Assigned tasks list**

![tasks-assigned-list](https://user-images.githubusercontent.com/8025853/204584572-8d52d78e-dbe2-49dc-b378-3e50078aaa5c.png)

**User details**

![user-details](https://user-images.githubusercontent.com/8025853/204584745-cb3e2fa3-22a7-47c1-85e1-ceb57c5262ae.png)

## Docker local setup

Make sure you have the latest versions of `Docker` and `Docker Compose` installed on your machine.

Copy variables from `.env.example` to `.env` file and adapt them to your needs.

Build and run containers (in daemon mode):
```
docker-compose up -d
```

Open the project at the address (if you haven't changed the default port):
```
https://127.0.0.1:701
```

## Security

If you discover a security vulnerability within this project, please send an email to taskana@fkv.pl.
