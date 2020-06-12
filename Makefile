#
# JBZoo Toolbox - Image
#
# This file is part of the JBZoo Toolbox project.
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#
# @package    Image
# @license    MIT
# @copyright  Copyright (C) JBZoo.com, All rights reserved.
# @link       https://github.com/JBZoo/Image
#

ifneq (, $(wildcard ./vendor/jbzoo/codestyle/src/init.Makefile))
    include ./vendor/jbzoo/codestyle/src/init.Makefile
endif


update: ##@Project Install/Update all 3rd party dependencies
	$(call title,"Install/Update all 3rd party dependencies")
	@echo "Composer flags: $(JBZOO_COMPOSER_UPDATE_FLAGS)"
	@composer update $(JBZOO_COMPOSER_UPDATE_FLAGS)


test-all: ##@Project Run all project tests at once
	@make test
	@make codestyle


test-phpunit-clean: ##@Tests Run unit-tests with TeamCity output
	$(call title,"PHPUnit - Running all tests")
	@echo "Config: $(JBZOO_CONFIG_PHPUNIT)"
	@php `pwd`/vendor/bin/phpunit                                 \
        --cache-result-file="$(PATH_BUILD)/.phpunit.result.cache" \
        --configuration="$(JBZOO_CONFIG_PHPUNIT)"                 \
        --order-by=random                                         \
        --colors=always