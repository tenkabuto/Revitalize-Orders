# Revitalize Orders
When transitioning from Jigoshop to WooCommerce, Jigoshop's deletion causes a purge of database items that are related to it. Luckily for people that had been using WooCommerce prior to Jigoshop's deletion, WooCommerce preserves most important information about your Products and Orders, but Orders' statuses get purged along with Jigoshop.

This plugin helps you restore your Orders' statuses so that you can get back to fulfilling orders.

## Roadmap
This code does/will do the following:

1. Query all orders
2. Check each for "zombie" (pending) status
3. IF zombie, retrieve last two comments
4. Search comments for last indicator of status update
    1. Match case
    2. IF match, put in array
5. Change order status to reflect "last" status update
    1. @"Last": First in array, as will be retrieving comments in descending order