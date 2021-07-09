
<p>
	<h1 align="center">
		![minical logo](https://user-images.githubusercontent.com/604232/125140727-03658d00-e0c8-11eb-9842-eb4c3fb44398.png)
		<br/>
	miniCal</h1>
</p>


<p align="center">
	<b>An Open-source Booking Management Platform</b> (http://www.minical.io)	
</p>

## Table of Contents

* [What is miniCal?](#what-is-minical)
* [Features](#features)
* [Documentation](#documentation)
* [Installation](#installation)
* [Demo](#demo)
* [Code of conduct](#code-of-conduct)
* [Contribute](#contribute)
* [How to develop an extension](#how-to-develop-an-extension)
* [Join the Community](#join-the-community)
* [miniCal Marketplace](#miniCal-marketplace)
* [Versioning](#versioning)
* [License](#license)

## What is miniCal?
miniCal is an open-source CRM with Booking Management baked-in. It's primarily designed for startups launching B2B SaaS business, but individual businesses can use it also.
Pretty much all businesses that's deal with CRM + appointment-scheduling can use miniCal. Some examples are: Hotels, hair salons, car rentals, and yoga studios.

<img src="https://snipboard.io/KZDfkU.jpg" alt="">


## Documentation
miniCal documentation is available here [minical.doc](https://github.com/minical/minical/wiki).

## Features
* <b>Online Booking Engine&nbsp;:</b>&nbsp;Accept online bookings from 3rd party websites.
* <b>Inventory&nbsp;:</b>&nbsp;Control room availabilities of your property.
* <b>CRM&nbsp;:</b>&nbsp;Manage customer profiles along with their account balances.
* <b>Intuitive Calendar&nbsp;:</b>&nbsp;Simple interface that provides quick overview of your property.
* <b>Payment&nbsp;:</b>&nbsp;Accept secure payments online.

...and plenty more extensions to add from our [marketplace](http://marketplace.minical.io/) !

![image](https://user-images.githubusercontent.com/604232/110357719-7236ba80-7ff0-11eb-9d75-e9aebfbba367.png)


## Installation

* Fork the repository `https://github.com/minical/minical` or clone it locally.
* Install the stable version of PHP 7.2.0, MySQL 5.0.4, and OS-specific dependency tools.
* Create a MySql database with any name.
* Do the basic [configuration updates](https://github.com/minical/minical/wiki/configuration) on the public->build.json file.
* Create a new file named ".env" by copying the .env.example file which is located in the root. Update database credentials including the above database name, for more details check the [.env example](https://github.com/minical/minical/wiki/.env-example).
* Run the "composer install" command on your project.
* Make your-server.com point to /public directory of miniCal installation.
* Install miniCal by going to `http://your-server.com/install.php` in your browser and create an admin account.
* you are done!

## Demo
Visit [miniCal Demo](http://demo.minical.io/).


## Code of Conduct
miniCal follows [Codeigniter Style Guide](https://codeigniter.com/userguide3/general/styleguide.html).

## Contribute

Any contribution for a new feature or an improvement will be appreciated.
###### To make a contribution:
1. Fork the repository, and edit.
2. Submit the pull request, please provide a comprehensive description of PR as commit message.
3. Any pull request that the reviewers don't find useful to miniCal will be rejected. We recommend you to talk to us first before working on a PR. Also, please ensure your code is following [Codeigniter Style Guide](https://codeigniter.com/userguide3/general/styleguide.html).

## How to develop an extension
Do you have an idea of an extension that might be a great addon to miniCal community?
Follow [Extension development guide](https://github.com/minical/minical/wiki).

## Join the Community
Get support. exchange ideas with our growing dev community. Join us on [Discord](https://discord.gg/a7B7RXZ3w5).

## miniCal Marketplace
Explore the extension of miniCal on [miniCal Marketplace](http://marketplace.minical.io/).

## Versioning

The version is broken down into 4 points e.g 1.2.3.4 We use MAJOR.MINOR.FEATURE.PATCH to describe the version numbers.

A MAJOR is very rare, it would only be considered if the source was effectively re-written or a clean break was desired for other reasons. This increment would likely break most 3rd party modules.

A MINOR is when there are significant changes that affect core structures. This increment would likely break some 3rd party modules.

A FEATURE version is when new extensions or features are added (such as a payment gateway, shipping module etc). Updating a feature version is at a low risk of breaking 3rd party modules.

A PATCH version is when a fix is added, it should be considered safe to update patch versions e.g 1.2.3.4 to 1.2.3.5

## License

[The Open Software License 3.0 (OSL-3.0)](https://github.com/minical/minical/blob/main/LICENSE)



