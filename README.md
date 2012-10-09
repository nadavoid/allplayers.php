## AllPlayers PHP Client

-----


This is the AllPlayers.com PHP client that uses AllPlayers' API to create, get and change user data, group data, etc.
Intended for developers to take advantage of the features that AllPlayers brings and interact with data in whatever way your application needs.

To start, clone this repository using git clone <repo url> and run <code>curl -s http://getcomposer.org/installer | php && ./composer.phar install</code> to install dependencies.

To test using phpunit, create a phpunit.xml file under main directory and let phpunit know to autoload dependencies and use your credentials:

----
    <phpunit bootstrap="./Tests/bootstrap.php" colors="true">
        <php>
            <server name="API_USER" value="<user>" />
            <server name="API_PASSWORD" value="<password>" />
            <server name="API_HOST" value="<host>" />
        </php>
        <testsuites>
            <testsuite name="guzzle-service">
                <directory suffix="Test.php">./Tests</directory>
            </testsuite>
        </testsuites>
    </phpunit>
----
