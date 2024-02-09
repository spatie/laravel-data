module.exports = {
    '**/*.php': ['php ./vendor/bin/php-cs-fixer fix --config .php-cs-fixer.dist.php --allow-risky=yes'],
};
