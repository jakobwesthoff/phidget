========
Phidgets
========

What is this all about?
=======================

Phidgets is a PHP_ based desktop widget system. It is a quite simple
framework, which lets you create really neat looking widgets for your desktop
in no time. And the best of all these widgets are written entirely in PHP_.


Take a look at what comes with it at no extra charge.
=====================================================

There is a set of some useful and graphically appealing widgets just packaged
with the current release. Lets take a look at what you can do with the base
package.


Starterbar
----------

The starterbar is an application runner widget, which animates the icons
representing your programs using a nice looking on mouse over zoom effect. It
is most likely that you have seen this kind of bar in other widget systems
too. 

.. image:: phidgets_starterbar.jpg
   :alt: Screenshot of the starterbar widget
   :align: center


Running elephants
-----------------

This widget was inspired by the `easter egg`__ in gnome which makes a `litle 
fish`__ swim across your desktop. Because this a PHP_ based desktop widget
what would better represent it than a walking php elephant.

.. image:: phidgets_elephant.jpg
   :alt: Screenshot of the elephants in action
   :align: center

__ http://www.google.de/search?q=%22free+the+fish%22+gnome
__ http://www.google.de/search?q=%22free+the+fish%22+gnome


Are there any special requirements?
===================================

Because this application makes heavy usage of gtk to display the widgets and
handle their events properly you need the `php gtk`__ extension installed for
any of the widgets to work. Although the current release version should be
working fine with phidgets you may want to take a look at the cvs repository
and build you own. There is quite a lot of development going on in this
project at the moment and therefore the prepackaged version seem to be quite
of date some time.

__ http://gtk.php.net

Phidgets is quite well documented just take a look in the docs folder. There
you will find documents on how to use phidgets as well as documents describing
how to write your own cool widget.

.. _PHP: http://php.net
