# Major changes in FOF 3.0

## Namespacing and versioning

**BACKWARDS COMPATIBILITY BREAK**

All classes in FOF 3.0 are now namespaced. This is breaking backwards compatibility. However, it's not the end of times: you just need to rename your classes, not rewrite everything from scratch! You can also run FOF 2.3 and 3.0 side-by-side on the same site without causing compatibility problems. This gives you until the end of life of Joomla! 3 to migrate your components to FOF 3.x. However, there is a catch. FOF 2.3 goes into maintenance mode. No new features and bug fixes go in it, just making sure that it still runs on Joomla! 3 without blowing up.

The namespacing is _intentionally_ making a bad choice for the top level namespace, giving it the name FOF30. As you might guess, future versions of FOF will change the top-level namespace to FOF31, FOF40 and so on. Moreover, each top-level namespace gets its own subdirectory inside libraries/fof (e.g. libraries/fof/fof30). This allows us to have multiple versions of FOF installed on a same site and have them run side-by-side.

The classes itself have been namespaced. Instead of having `F0FController` we now have `FOF30\Controller\Controller`. The FOF folder and class file structure has remained the same.

### Including FOF 3

Including FOF 3.0 requires a small change in your code. You now have to use:

	if (!defined('FOF30_INCLUDED'))
	{
		require_once JPATH_LIBRARIES . '/fof/fof30/include.php';
	}

As you can see, the `FOF_INCLUDED` constant is renamed to `FOF30_INCLUDED`, containing the major/minor version family of FOF in its name. Moreover, as described above, the path to FOF's files also indicates its version family. Remember to change this include and the name of the `FOF_INCLUDED` constant in your code.

### Migrating your code to FOF 3

FOF 3 includes a set of "training wheels" to be used **IN DEVELOPMENT ONLY**. I am serious about this. Do NOT use in production or you'll be probably breaking FOF 2.3 an earlier components.

The "training wheels" in question can be accessed by including
	
	require_once JPATH_LIBRARIES . '/fof/fof30/legacy/f0fcompat.php';
	
This file creates class aliases with the legacy F0FSomething names to their namespaced counterparts. This will allow you to run your existing component against the FOF 3.0 library in use instead of FOF 2.x. The idea is that you can focus on solving backwards compatibility issues BEFORE you have to deal with namespacing. Trust me, this will save your sanity: I've written this file to help me preserve a modicum of sanity while rewriting the FOF Unit Tests.

Once you have your component running in FOF 3, please start replacing all the F0FSomething references in your code with their namespaced \FOF30\Something\Something counterparts. Finally remove the inclusion of the `f0fcompat.php` file and then you're ready to ship your code.

## Got rid of the "integration" abstraction layer

**BACKWARDS COMPATIBILITY BREAK**

FOF 2.2 and 2.3 had split their platform abstraction layer into two parts, the Platform package and the Integration package. The Platform package was what FOF and you were directly using, e.g. `F0FPlatform::getInstance()->isCliAdmin()`. The Integration package was used internally by F0FPlatform to perform the actual work. The original idea was that this separation would allow FOF to run easier on different Joomla! versions. However, this was found to be highly unnecessary and have a slight performance hit.

As far as you are concerned, you can still use the Platform package as you used to. There is no backwards incompatible change to its interface.

What _has_ changed is how you create your custom integration. Most of you have no idea what this means and I seriously doubt that anyone has made a custom platform integration. So, unless you have an idea of what I'm talking about and have written your own Integration classes you needn't worry: the backwards incompatible change doesn't affect you.

# TO-DO for FOF 3.0

We only have Joomla! integration. Fold it back to the Platform abstraction layer.

## More file formats for RAD configuration (fof.xml)

FOF 2.x has only supported the XML format for the RAD configuration file(fof.xml).

In FOF 3.x you can use XML (fof.xml) or JSON (fof.json) files. The XML file takes precedence over the JSON file. The first one found is the one to be used. This means that if both `fof.xml` and `fof.json` are present only `fof.xml` will be used. If `fof.xml` doesn't exist but `fof.json` exists then `fof.json` is used.

## Caching database record iterator.

The database record iterator we had on FOF 2.3 only supports browsing forward, i.e. reading the next record. A new lazy loading iterator is implemented that loads X items in memory, lets you move in any direction and then load another X items and so on. Pretty much how Laravel does it.

## ORM improvements

Eager loading, magic accessors for relations

## Strapper in core (TENTATIVE)

Allows us to add FOF-specific CSS and JS.

## Templating (TENTATIVE)

Shortcuts in the PHP templates, e.g. `@text(SOMETHING)` => `<?php echo JText::_('SOMETHING') ?>

## **B/C BREAK – MAYBE** MVC Autoloader

You may gave observed that we have a F0FAutoloaderComponent which is not activated by default. The reason is that it's using the evil eval() to work around the fact that PHP doesn't allow class aliases to know whether they are called through their alias or their real class name. The only way around this is following a deprecation route you will not like much. For starters we can modify all getTmpInstance/getAnInstance methods to use the class autoloader, but if the class is not found create a new object using the default F0FModel/Controller/View/Table/Dispatcher classes. This will move a lot of code from these classes to the autoloader. The second step (for FOF4 or later) would be to get rid of the getTmpInstance methods altogether and require class files to exist at all times. That's a pity, as it makes FOF a bit less RAD and that's why I've not decided to go there yet.

## **B/C BREAK** $config becomes a DI container.

Right now we are passing around a $config array from Dispatcher to Controller to Model and View. At the same time we have the Integration and Platform packages to provide abstraction for certain things like user management, filesystem handling etc. Moreover we have some static stuff which affect FOF globally, e.g. the choice of renderer, adding words in the Inflector etc. Not to mention the performance hit of having to go through the fof.xml parsing several times (yikes!). You know how this can all be solved? Using a DI container. I've already tinkered with Pimple which is a simple and effective solution. Even better? It exposes an array interface, so the learning curve will not be as steep as using, say, the DI container included in Joomla! 3.2.

## Basic route helper.

I am talking about very naïve routing here, in the form of /component_name/view_name/some_slug. We can do that by assigning a slug field for every Table. If none is specified and the Table doesn't have a "slug" field alias we will use the primary key. This is possible because each View has a Model which has a Table which defines the slug / primary key. Handling menus where you select a specific item is a different story and we'll have to use conventions – or you get to write your own custom routers, if you are so inclined.

## Advanced route helper.

This is a bit of a stretch. It will let you define custom routing patterns and the way to decode them. I am still thinking it through. Consider it more of a wish list item than something I am sure I can build :)

## Scaffolding

This is an optional feature which you'll be able to enable in fof.xml. It will create automatic back-end browse and edit views based on your table structure and possibly some hints in fof.xml about the intended field type. This is not intended to replace or augment the Form package. It is intended as a quick way to start entering data in the database without having to manually create a form file. Well, it could optionally write out the form file it generates on the fly to help you get started with form generation really fast.

In the scaffolding, if we could have a script that could generated all define languages and the languages files that will save lot of time.
+ this will prevent to forget some define like LBL_XXXX (redirect save message)