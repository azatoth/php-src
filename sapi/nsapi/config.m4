dnl
dnl $Id$
dnl

AC_MSG_CHECKING(for NSAPI support)
AC_ARG_WITH(nsapi,
[  --with-nsapi=DIR        Build PHP as NSAPI module for use with iPlanet.],[
  PHP_NSAPI=$withval
],[
  PHP_NSAPI=no
])
AC_MSG_RESULT($PHP_NSAPI)

if test "$PHP_NSAPI" != "no"; then
  if test ! -d $PHP_NSAPI/bin ; then
    AC_MSG_ERROR(Please specify the path to the root of your Netscape server using --with-nsapi=DIR)
  fi
  AC_MSG_CHECKING(for NSAPI include files)
  if test -d $PHP_NSAPI/include ; then
    NSAPI_INCLUDE=$PHP_NSAPI/include
    AC_MSG_RESULT(Netscape-Enterprise/3.x style)
  fi
  if test -d $PHP_NSAPI/plugins/include ; then
    NSAPI_INCLUDE="$NSAPI_INCLUDE -I$PHP_NSAPI/plugins/include"
    AC_MSG_RESULT(iPlanet/4.x style)
  fi

  if test "$NSAPI_INCLUDE" = ""; then
    AC_MSG_ERROR(Please check you have nsapi.h in either DIR/include or DIR/plugins/include)
  fi

  PHP_ADD_INCLUDE($NSAPI_INCLUDE)
  PHP_BUILD_THREAD_SAFE
  AC_DEFINE(HAVE_NSAPI,1,[Whether you have a Netscape Server])
  PHP_SAPI=nsapi
  PHP_BUILD_SHARED
  INSTALL_IT="\$(INSTALL) -m 0755 $SAPI_SHARED \$(INSTALL_ROOT)$PHP_NSAPI/bin/"
fi


dnl ## Local Variables:
dnl ## tab-width: 4
dnl ## End:
