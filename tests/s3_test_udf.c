#include "sphinxudf.h"

sphinx_int64_t test_udf_ver()
{
    return SPH_UDF_VERSION;
}

sphinx_int64_t my_udf(SPH_UDF_INIT* init, SPH_UDF_ARGS* args, char* error_flag)
{
    (void) init;
    (void) args;
    (void) error_flag;

    return 42;
}
