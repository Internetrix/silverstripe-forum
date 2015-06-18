# Forum Module (Internetrix)

## Maintainer Contact

 * Stewart Wilson <stewart.wilson (at) internetrix (dot) com (dot) au>
 
## Requirements

 * SilverStripe 3.1.0+

## Installation

Install the module as normal. The forum uses groups to define who can post to and moderate those forums. Create a group for each forum you create
or it is possible for a number of forums to share the same group.

### Setting the groups

* Create the groups in the Security admin, make sure you select "Is Forum Group"
* Go to the Forum Holder page and select which groups will be shown on the registration form
* On each Forum page, you can then select which group will control the permissions and choose "Only these people"
* Tick "Users Require Moderation" if a moderator needs to approve membership

### Post moderation

* If the posts need moderation, tick "Posts Require Moderation" in the Forum Settings of the Forum page.

### Enable/disable embedding

* You can enable/disable embedding by picking "Allow Media to be embedded in posts". It can be disabled on a per-thread basis as well in the Moderation admin (on the front end)

### Set Email From Address

In your config.yml

	Forum:
		send_email_from: 'youremailhere@example.com'