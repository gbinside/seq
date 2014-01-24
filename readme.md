#Rel

rel converts .rel files containing a series of conceptual
relations into graphviz graphs. It can produce sequence diagrams.
It's in very early stages of development. It's based on php.

##Usage

Right now, it's hardwired to run oauth.rel, so you just:

    php rel.php

...and it generates:

* `tempfile.dot` a graphviz file
* `tempfile.png` the result of running tempfile.dot trough graphviz (providing it's installed)

Right now it also tries to show it using `explorer.exe` (yes, I do windows)
