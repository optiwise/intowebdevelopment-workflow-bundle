<?php

namespace IntoWebDevelopment\WorkflowBundle;

class Events
{
    public const PROCESS_FLOW_ALLOWED_TO_STEP = 'workflow.allowed_to_step';

    public const PROCESS_FLOW_BEFORE_ACTION = 'workflow.before_action_execution';

    public const PROCESS_FLOW_AFTER_ACTION = 'workflow.after_action_executed';

    public const PROCESS_FLOW_STEPPING_COMPLETED = 'workflow.step_completed';

    public const BEFORE_VALIDATE_NEXT_STEP = 'workflow.validate_next_step';

    const BEFORE_VALIDATE_CURRENT_STEP = 'workflow.validate_current_step';
}