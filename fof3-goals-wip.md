# √ **B/C BREAK** Namespacing (sort of – intentionally "bad" use).

Instead of going with full and proper namespacing of the classes (which would require you to rewrite everything) I am thinking of a purposely "bad" use of namespaces for versioning reasons. FOF 3 will be in the namespace fof30. You will need to do some mass search and replace but it won't be too bad. The idea is that every time we break b/c we create a new namespace. Moreover, each namespace gets its own subdirectory inside libraries/fof allowing you to have multiple FOF versions on a site at the same time.

Most likely the namespacing will be something like \FOF\FOF30\Foo\Bar\Baz

As for the file naming, I'd say let's not use uppercase filenames. I've tried that already and it creates some interesting issues when you're developing on a Mac / Win machine and deploying on a Linux server.

The folder structure will most likely be <libraries>/fof/fof30/foo/bar/baz.php. Since we'll be in a private subdirectory with no naming clashes there's no longer a need to use f-zero-f in the directory name or namespaces.

# Get rid of the "integration" abstraction layers

We only have Joomla! integration. Fold it back to the Platform abstraction layer.

# JSON instead of XML for fof.xml (fof.json) and form files

Maybe we could generalise it even more, using JRegistry to get the data from the configuration file. The load order would be XML, JSON then other formats e.g. YAML and INI. The first one found wins.

# Caching database record iterator.

The database record iterator we have right now only supports browsing forward, i.e. reading the next record. I am thinking that we can have a lazy loading iterator that loads X items in memory, lets you move in any direction and then load another X items and so on. Pretty much how Laravel does it.

# ORM improvements

Eager loading, magic accessors for relations

# Strapper in core (TENTATIVE)

Allows us to add FOF-specific CSS and JS.

# Templating (TENTATIVE)

Shortcuts in the PHP templates, e.g. `@text(SOMETHING)` => `<?php echo JText::_('SOMETHING') ?>

# **B/C BREAK – MAYBE** MVC Autoloader

You may gave observed that we have a F0FAutoloaderComponent which is not activated by default. The reason is that it's using the evil eval() to work around the fact that PHP doesn't allow class aliases to know whether they are called through their alias or their real class name. The only way around this is following a deprecation route you will not like much. For starters we can modify all getTmpInstance/getAnInstance methods to use the class autoloader, but if the class is not found create a new object using the default F0FModel/Controller/View/Table/Dispatcher classes. This will move a lot of code from these classes to the autoloader. The second step (for FOF4 or later) would be to get rid of the getTmpInstance methods altogether and require class files to exist at all times. That's a pity, as it makes FOF a bit less RAD and that's why I've not decided to go there yet.

# **B/C BREAK** $config becomes a DI container.

Right now we are passing around a $config array from Dispatcher to Controller to Model and View. At the same time we have the Integration and Platform packages to provide abstraction for certain things like user management, filesystem handling etc. Moreover we have some static stuff which affect FOF globally, e.g. the choice of renderer, adding words in the Inflector etc. Not to mention the performance hit of having to go through the fof.xml parsing several times (yikes!). You know how this can all be solved? Using a DI container. I've already tinkered with Pimple which is a simple and effective solution. Even better? It exposes an array interface, so the learning curve will not be as steep as using, say, the DI container included in Joomla! 3.2.

# Basic route helper.

I am talking about very naïve routing here, in the form of /component_name/view_name/some_slug. We can do that by assigning a slug field for every Table. If none is specified and the Table doesn't have a "slug" field alias we will use the primary key. This is possible because each View has a Model which has a Table which defines the slug / primary key. Handling menus where you select a specific item is a different story and we'll have to use conventions – or you get to write your own custom routers, if you are so inclined.

# Advanced route helper.

This is a bit of a stretch. It will let you define custom routing patterns and the way to decode them. I am still thinking it through. Consider it more of a wish list item than something I am sure I can build :)

# Scaffolding

This is an optional feature which you'll be able to enable in fof.xml. It will create automatic back-end browse and edit views based on your table structure and possibly some hints in fof.xml about the intended field type. This is not intended to replace or augment the Form package. It is intended as a quick way to start entering data in the database without having to manually create a form file. Well, it could optionally write out the form file it generates on the fly to help you get started with form generation really fast.

In the scalfolding, if we could have a script that could generated all define languages and the languages files that will save lot of time.
+ this will prevent to forget some define like LBL_XXXX (redirect save message)

# Get rid of Table (TENTATIVE)

Roll them into models