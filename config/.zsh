#!/bin/zsh
#
# Copyright (c) 2022. Der Code ist geistiges Eigentum von Tim Anthony Alexander.
# Der Code wurde geschrieben unter dem Arbeitstitel und im Auftrag von coalla.
# Verwendung dieses Codes außerhalb von coalla von Dritten ist ohne ausdrückliche Zustimmung von Tim Anthony Alexander nicht gestattet.
#

################################################################
######                    coalla zshrc                    ######
################################################################


########################
##       Aliases      ##
########################
alias l='ls --color=auto -lhFr'
alias cls='clear && echo'
alias c='cls'
alias cat='batcat'
alias bat='batcat'
alias gs='gst'
alias dumpautoload='composer -n dumpautoload'
alias unit='composer -n unit && echo Done'
alias fix='composer -n fix && echo Done'
alias int='cd /var/integration/'
alias latest='cd /var/latest/'
alias staging='cd /var/staging/'
alias prod='cd /var/production/'
alias dev='cd /var/dev/'
alias tbs='cd /var/tbs/'
alias intl='int && l'
alias stagingl='staging && l'
alias prodl='prod && l'
alias gint='git switch integration'
alias gstaging='git switch staging'
alias gprod='git switch production'
alias gdev='git switch development'
alias gsiap='git switch integration && git pull'
alias sites='cd /etc/nginx/sites-available'
alias root='cd /root'
alias prodzsh='vi /var/production/.zsh'
alias showlogs='l|grep log'
alias showrlogs='l -R|grep log'
alias v='vi'

########################
##       Exports      ##
########################
export HISTFILE="$HOME/.zsh_history"

########################
##      Shortcuts     ##
########################
function addssh() { eval $(ssh-agent);ssh-add ~/.ssh/id_rsa; }
function zshrc() { vi /var/integration/config/.zsh; echo "Datei auf Integration bearbeitet. Bearbeite die Datei im Repo, um die Prod-Version dieser Datei zu bearbeiten."; }
function sourcezsh() { source ~/.zshrc; echo "Von zshrc geladen."; }
function srczsh() { sourcezsh; }

########################
##      RELEASES      ##
########################

function createversion() {
  cd /var/latest || exit
  cd coalla-api || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/integration
  git push
  cd ../coalla-frontend || exit
  git fetch
  git rebase origin/integration
  git push
  php /var/latest/coalla-api/scripts/crack.php
}

function releaseintegration() {
  cd /var/integration || exit
  cd coalla-api || exit
  git reset HEAD --hard
  git pull
  composer -n dumpautoload >/dev/null 2>&1
  composer install >/dev/null 2>&1
  composer migrations 2>/dev/null
  cd ../coalla-frontend || exit
  git reset HEAD --hard
  git pull
  git push
  ## coalla-frontend is an npm project so install and build
  npm install --legacy-peer-deps
  npm run build
}

function releasestaging() {
  cd /var/staging || exit
  cd coalla-api || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/latest
  git push
  composer -n dumpautoload >/dev/null 2>&1
  composer install >/dev/null 2>&1
  composer migrations 2>/dev/null
  cd ../coalla-frontend || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/latest
  git push
  ## coalla-frontend is an npm project so install and build
  npm install --legacy-peer-deps
  npm run build
  php /var/staging/coalla-api/scripts/crack.php
}

function releaseproduction() {
  cd /var/production || exit
  cd coalla-api || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/latest
  git push
  composer -n dumpautoload >/dev/null 2>&1
  composer install >/dev/null 2>&1
  composer migrations 2>/dev/null
  cd ../coalla-frontend || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/latest
  git push
  ## coalla-frontend is an npm project so install and build
  npm install --legacy-peer-deps
  npm run build
  php /var/production/coalla-api/scripts/crack.php
}

function createAPIversion() {
  cd /var/latest || exit
  cd coalla-api || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/integration
  git push
}

function releaseAPIintegration() {
  cd /var/integration || exit
  cd coalla-api || exit
  git reset HEAD --hard
  git pull
  git push
  composer -n dumpautoload >/dev/null 2>&1
  composer install >/dev/null 2>&1
  composer migrations 2>/dev/null
}

function releaseAPIstaging() {
  cd /var/staging || exit
  cd coalla-api || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/latest
  git push
  composer -n dumpautoload >/dev/null 2>&1
  composer install >/dev/null 2>&1
  composer migrations 2>/dev/null
}

function releaseAPIproduction() {
  cd /var/production || exit
  cd coalla-api || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/latest
  git push
  composer -n dumpautoload >/dev/null 2>&1
  composer install >/dev/null 2>&1
  composer migrations 2>/dev/null
}

function createFrontendversion() {
  cd /var/latest || exit
  cd coalla-frontend || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/integration
  git push
}

function releaseFrontendintegration() {
  cd /var/integration || exit
  cd coalla-frontend || exit
  git reset HEAD --hard
  git pull
  git push
  ## coalla-frontend is an npm project so install and build
  npm install --legacy-peer-deps
  npm run build
}

function releaseFrontendstaging() {
  cd /var/staging || exit
  cd coalla-frontend || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/latest
  git push
  ## coalla-frontend is an npm project so install and build
  npm install --legacy-peer-deps
  npm run build
}

function releaseFrontendproduction() {
  cd /var/production || exit
  cd coalla-frontend || exit
  git reset HEAD --hard
  git fetch
  git rebase origin/latest
  git push
  ## coalla-frontend is an npm project so install and build
  npm install --legacy-peer-deps
  npm run build
}

releaseAll() {
  releaseAPIintegration > /dev/null 2>&1
  releaseFrontendintegration > /dev/null 2>&1
  createversion > /dev/null 2>&1
  releaseAPIstaging > /dev/null 2>&1
  releaseFrontendstaging > /dev/null 2>&1
  releaseAPIproduction > /dev/null 2>&1
  releaseFrontendproduction > /dev/null 2>&1
}

releaseAllAPI() {
  releaseAPIintegration
  createAPIversion
  releaseAPIstaging
  releaseAPIproduction
}

releaseAllFrontend() {
  releaseFrontendintegration
  createFrontendversion
  releaseFrontendstaging
  releaseFrontendproduction
}


########################
##    Other stuff     ##
########################
source ~/.oh-my-zsh/custom/themes/powerlevel10k/powerlevel10k.zsh-theme
if [[ -r "${XDG_CACHE_HOME:-$HOME/.cache}/p10k-instant-prompt-${(%):-%n}.zsh" ]]; then
  source "${XDG_CACHE_HOME:-$HOME/.cache}/p10k-instant-prompt-${(%):-%n}.zsh"
fi
[[ ! -f ~/.p10k.zsh ]] || source ~/.p10k.zsh
