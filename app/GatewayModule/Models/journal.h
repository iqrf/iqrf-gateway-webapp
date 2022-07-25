
#define FFI_LIB "libsystemd.so.0"

typedef struct sd_journal sd_journal;
typedef unsigned long int uint64_t;

int sd_journal_open(sd_journal **ret, int flags);
int sd_journal_next(sd_journal *j);

int sd_journal_test_cursor(sd_journal *j, const char *cursor);

int sd_journal_previous_skip(sd_journal *j, uint64_t skip);
int sd_journal_seek_cursor(sd_journal *j, const char *cursor);
int sd_journal_seek_tail(sd_journal *j);
