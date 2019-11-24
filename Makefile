#
# JBZoo Image
#
# This file is part of the JBZoo CCK package.
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#
# @package    Image
# @license    MIT
# @copyright  Copyright (C) JBZoo.com, All rights reserved.
# @link       https://github.com/JBZoo/Image
#

.PHONY: build update test-all validate autoload test phpmd phpcs phpcpd phploc reset coveralls

build: update

test-all:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Run all tests \033[0m"
	@make validate test phpcpd phploc

update:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Update project \033[0m"
	@composer update --optimize-autoloader --no-interaction
	@echo ""

validate:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Composer validate \033[0m"
	@composer check-platform-reqs --no-interaction
	@composer validate --no-interaction
	@echo ""

autoload:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Composer autoload \033[0m"
	@composer dump-autoload --optimize --no-interaction
	@echo ""

test:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Run unit-tests \033[0m"
	@php ./vendor/phpunit/phpunit/phpunit --configuration ./phpunit.xml.dist
	@echo ""

test-x:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Run unit-tests with XDebug \033[0m"
	@php-x ./vendor/phpunit/phpunit/phpunit --configuration ./phpunit.xml.dist --verbose
	@echo ""

phpmd:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Check PHPmd \033[0m"
	@php ./vendor/phpmd/phpmd/src/bin/phpmd ./src text  \
         ./vendor/jbzoo/misc/phpmd/jbzoo.xml --verbose

phpcs:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Check Code Style \033[0m"
	@php ./vendor/squizlabs/php_codesniffer/scripts/phpcs ./src  \
        --extensions=php                                         \
        --standard=./vendor/jbzoo/misc/phpcs/JBZoo/ruleset.xml   \
        --report=full
	@echo ""

phpcpd:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Check Copy&Paste \033[0m"
	@php ./vendor/sebastian/phpcpd/phpcpd ./src --verbose
	@echo ""

phploc:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Show stats \033[0m"
	@php ./vendor/phploc/phploc/phploc ./src --verbose
	@echo ""

reset:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Hard reset \033[0m"
	@git reset --hard

clean:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Cleanup project \033[0m"
	@rm -rf ./vendor/
	@rm -f ./composer.lock

coveralls:
	@echo "\033[0;33m>>> >>> >>> >>> >>> >>> >>> >>> \033[0;30;46m Send coverage to coveralls.io \033[0m"
	@php ./vendor/satooshi/php-coveralls/bin/coveralls --verbose
	@echo ""
