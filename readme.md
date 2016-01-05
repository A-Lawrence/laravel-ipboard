## Laravel IPBoard API
[![Packagist License](https://poser.pugx.org/alawrence/laravel-ipboard/license.png)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://poser.pugx.org/alawrence/laravel-ipboard/version.png)](https://packagist.org/packages/A-Lawrence/laravel-ipboardapi)
[![Latest Unstable Version](https://poser.pugx.org/alawrence/laravel-ipboard/v/unstable)](https://packagist.org/packages/A-Lawrence/laravel-ipboardapi)
[![Total Downloads](https://poser.pugx.org/alawrence/laravel-ipboard/d/total.png)](https://packagist.org/packages/A-Lawrence/laravel-ipboardapi)

This package includes accessor methods for all common IPBoard API calls:
 - Members
 - Forum Posts
 - Forum Topics

## Installation

Require this package with composer:

```
composer require alawrence/laravel-ipboard
```

After updating composer, add this package's ServiceProvider to the providers array in config/app.php

### Laravel 5.x:

ServiceProvider:
```php
Alawrence\Ipboard\ServiceProvider::class,
```

Facade:
```php
'Ipboard' => Alawrence\Ipboard\Facade::class,
```

In order to set the required variables for your instance of IPBoard, you must first publish the configuration files:

```
php artisan vendor:publish
```

## Usage

To utilise any of the API endpoints, refer to the list of available calls.

### core/members

```php
$ipboard = new IPBoard();

$recentlyJoined = $ipboard->getMembersByPage("date", "desc");
$allMembers = $ipboard->getMembersAll();
$singleMember = $ipboard->getMemberById(2011);

$newMember = $ipboard->createMember("Test Api User", "test-user@gmail.com", "this_is_My_password!"); // Will be added to default group.
$anotherMember = $ipboard->createMember("Test Api User 2", "test-user-2@gmail.com", "this_is_not_secret", 24); // Will be added to group 24.

$updateMember = $ipboard->updateMember(2011, ["name" => "This Is THe New Name", "password" => "The new password" => "email" => "im_sleeping@gmail.com"]);

$ipboard->deleteMemberById(2011);
```

### forums/posts

```php
$ipboard = new IPBoard();

$recentPosts = $ipboard->getForumPostsByPage(["sortBy" => "date", "sortDir" => "desc"]); // Refer to IPBoard API reference for more search criteria.
$allPosts = $ipboard->getForumPostsAll(); // I would think carefully before doing this.

$singlePost = $ipboard->getForumPostById(12); // Get post ID 12.

$newPost = $ipboard->createForumPost(5, 2011, "<p>This is <strong>my</strong> HTML post.</p>"); // Topic 5, author 2011.   Refer to IPBoard API for more data you can provide.
$newGuestPost = $ipboard->createForumPost(5, 0, "<p>This is a <em>guest</em> post.</p>", ["author_name" => "My User's Guest Name"]); // Topic 5, author 0 with specified name.   Refer to IPBoard API for more data you can provide.

$updatedPost = $ipboard->updateForumPost(567, ["post" => "<p>This content has been removed.</p>"]); // Update post 567.  Refer to IPBoard API for more data you can provide.

$ipboard->deleteForumPostById(567);
```

### forums/topics

```php
$ipboard = new IPBoard();

$recentTopics = $ipboard->getForumTopicsByPage(["sortBy" => "date", "sortDir" => "desc"]); // Refer to IPBoard API reference for more search criteria.
$allTopics = $ipboard->getForumTopicsAll(); // I would think carefully before doing this.

$singleTopic = $ipboard->getForumTopicById(53); // Get topic ID 53;

$newTopic = $ipboard->createForumTopic(2, 2011, "My New Post Title", <p>This is <strong>my</strong> HTML post.</p>"); // Forum 2, author 2011.   Refer to IPBoard API for more data you can provide.
$newGuestTopic = $ipboard->createForumTopic(2, 0, "My guest title", <p>This is a <em>guest</em> post.</p>", ["author_name" => "My User's Guest Name"]); // Forum 2, author 0 with specified name.   Refer to IPBoard API for more data you can provide.

$updateTopic = $ipboard->updateForumTopic(56, ["title" => "Removed title", "post" => "<p>This content has been removed.</p>"]); // Update topic 56.  Refer to IPBoard API for more data you can provide.

$ipboard->deleteForumTopicById(567);
```

## Contribution

I appreciate there are elements of the API that haven't been implemented, as my license doesn't contain them.  If you wish to submit a PR I'll gladly accept.
