<?php

namespace IntoWebDevelopment\WorkflowBundle;

class Events
{
    const PROCESS_FLOW_ALLOWED_TO_STEP = 'workflow.allowed_to_step';

    const PROCESS_FLOW_BEFORE_ACTION = 'workflow.before_action_execution';

    const PROCESS_FLOW_AFTER_ACTION = 'workflow.after_action_executed';

    const PROCESS_FLOW_STEPPING_COMPLETED = 'workflow.step_completed';

    const BEFORE_VALIDATE_NEXT_STEP = 'workflow.validate_next_step';

    const BEFORE_VALIDATE_CURRENT_STEP = 'workflow.validate_current_step';
}