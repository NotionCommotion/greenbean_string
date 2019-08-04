Composer packages:

Install:
composer require concrete5/your_package_name
./vendor/bin/concrete5 c5:package-install your_package_name

Create new:
Using this project as a skeleton
composer create-project concrete5/your_package_name
Once this is done, modify the composer.json to have information about your project and an updated name. Then set up your VCS
Add to github and then packaglist.


Create single pages for frontend: Reports
Create single pages for backend: GB Data Integrator, and sub-pages Points, Charts, Data Sources, Settings, Manual, Help Desk.
Create routes to API for both front and back end.


mkdir gb_data_integrator
mkdir gb_data_integrator/single_pages
mkdir gb_data_integrator/single_pages/dashboard
mkdir gb_data_integrator/single_pages/dashboard/greenbean
mkdir gb_data_integrator/controllers
mkdir gb_data_integrator/controllers/single_page
mkdir gb_data_integrator/controllers/single_page/dashboard
mkdir gb_data_integrator/controllers/single_page/dashboard/greenbean




# Sample Composer Package
This project is a concrete5 sample package that is powered entirely by [composer](https://getcomposer.org).

To install this package on a [composer based concrete5](https://github.com/concrete5/composer) site, make sure you already have `composer/installers` then run:

```sh
$ composer install concrete5/your_package_name
```

Then install the package

```sh
$ ./vendor/bin/concrete5 c5:package-install your_package_name
```


----

# Using this project as a skeleton

First, use `composer create-project` to begin your own package project.

```php
$ composer create-project concrete5/your_package_name
```

Once this is done, modify the `composer.json` to have information about your project and an updated name.
Then set up your VCS

```php
git init
git remote add origin git@github.com/youraccount/yourrepository
git add .
git commit -m "Initial Commit"
git push
```

Finally, add your git repository to a [composer repository](https://packagist.org/). And that's it!
