
	/* $Id: fpm_process_ctl.h,v 1.6 2008/07/20 21:33:10 anight Exp $ */
	/* (c) 2007,2008 Andrei Nigmatulin */

#ifndef FPM_PROCESS_CTL_H
#define FPM_PROCESS_CTL_H 1

#include "fpm_events.h"

/* spawn max 32 children at once */
#define FPM_MAX_SPAWN_RATE (32)
/* 1s (in ms) heartbeat for idle server maintenance */
#define FPM_IDLE_SERVER_MAINTENANCE_HEARTBEAT (1000)
/* 130ms heartbeat for pctl */
#define FPM_PCTL_HEARTBEAT (130)

struct fpm_child_s;

void fpm_pctl(int new_state, int action);
int fpm_pctl_can_spawn_children();
int fpm_pctl_kill(pid_t pid, int how);
void fpm_pctl_kill_all(int signo);
void fpm_pctl_heartbeat(struct fpm_event_s *ev, short which, void *arg);
void fpm_pctl_perform_idle_server_maintenance_heartbeat(struct fpm_event_s *ev, short which, void *arg);
int fpm_pctl_child_exited();
int fpm_pctl_init_main();


enum {
	FPM_PCTL_STATE_UNSPECIFIED,
	FPM_PCTL_STATE_NORMAL,
	FPM_PCTL_STATE_RELOADING,
	FPM_PCTL_STATE_TERMINATING,
	FPM_PCTL_STATE_FINISHING
};

enum {
	FPM_PCTL_ACTION_SET,
	FPM_PCTL_ACTION_TIMEOUT,
	FPM_PCTL_ACTION_LAST_CHILD_EXITED
};

enum {
	FPM_PCTL_TERM,
	FPM_PCTL_STOP,
	FPM_PCTL_CONT,
	FPM_PCTL_QUIT
};

#endif

