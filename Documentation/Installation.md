# Installation (dev env)

Download and install [VirtualBox](https://www.virtualbox.org/wiki/Downloads)

Download and Install [Vagrant](https://www.vagrantup.com/downloads.html)

Clone repository (if didn't before)
```bash
git clone --recursive https://github.com/Alpha-Directorate/AlphaSSS alphasss.dev
```
Go to project folder
```bash
cd alphasss.dev
```
Add CodeSniffer hooks to git
```bash
cp ./hooks/pre-commit .git/hooks
chmod +x .git/hooks/pre-commit
```
Open .git/hooks/pre-commit file for edit and setup path to CodeSniffer like:
```nano
PHPCS_BIN=/full/path/to/folder/alphasss.dev/vendor/bin/phpcs
```

Run vagrant
```bash
vagrant up
```

Add to your hosts file a record:
```nano
 192.168.33.10 alphasss.dev
```

## System Requirements

1. Reasonably powerfull x86 hardware. Any recent Intel or AMD processor should do.

2. Memory. You will need at least 512MB of RAM (but probably more, and the more the better).

3. Hard disc space. By default project development enviropment size growing dynamicly so we recomend reserve minimum free 1GB for it.

4. If you need change virtual hardware configuration values please check this [page](https://docs.vagrantup.com/v2/virtualbox/configuration.html).


## Need Help?

Let us have it! Don't hesitate to open a new issue on GitHub if you run into trouble or have any tips that we need to know.

