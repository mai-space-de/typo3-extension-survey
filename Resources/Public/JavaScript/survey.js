(() => {
    const validateStep = (stepElement) => {
        const required = stepElement.querySelectorAll('[data-survey-required="1"]');
        let valid = true;

        required.forEach((input) => {
            const name = input.name;
            if (!name) return;

            if (input.type === 'radio' || input.type === 'checkbox') {
                const group = stepElement.querySelectorAll(`[name="${CSS.escape(name)}"]`);
                const checked = Array.from(group).some((el) => el.checked);
                if (!checked) {
                    valid = false;
                    group.forEach((el) => el.closest('.mai-survey-question__option')?.classList.add('is-invalid'));
                } else {
                    group.forEach((el) => el.closest('.mai-survey-question__option')?.classList.remove('is-invalid'));
                }
            } else if (input.tagName === 'TEXTAREA' || input.type === 'text' || input.type === 'date') {
                if (input.value.trim() === '') {
                    valid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            }
        });

        return valid;
    };

    const updateWizard = (wizard, currentStep) => {
        const steps = Array.from(wizard.querySelectorAll('[data-survey-step]'));
        const totalSteps = steps.length || Number.parseInt(wizard.dataset.totalSteps ?? '1', 10);
        const boundedStep = Math.min(Math.max(currentStep, 1), totalSteps);

        steps.forEach((stepElement, index) => {
            stepElement.hidden = index + 1 !== boundedStep;
        });

        const percentage = Math.round((boundedStep / totalSteps) * 100);
        const progressBar = wizard.querySelector('[data-survey-progress-bar]');
        const progressText = wizard.querySelector('[data-survey-progress-text]');

        if (progressBar) {
            progressBar.style.width = `${percentage}%`;
        }

        if (progressText) {
            progressText.textContent = progressText.dataset.template
                ? progressText.dataset.template.replace('%1$s', String(boundedStep)).replace('%2$s', String(totalSteps))
                : `Step ${boundedStep} of ${totalSteps}`;
        }

        const prevBtn = wizard.querySelector('[data-survey-prev]');
        const nextBtn = wizard.querySelector('[data-survey-next]');
        const submitBtn = wizard.querySelector('[data-survey-submit]');

        if (prevBtn) prevBtn.hidden = boundedStep === 1;
        if (nextBtn) nextBtn.hidden = boundedStep === totalSteps;
        if (submitBtn) submitBtn.hidden = boundedStep !== totalSteps;

        wizard.dataset.currentStep = String(boundedStep);
    };

    const initScaleOutputs = (wizard) => {
        wizard.querySelectorAll('input[type="range"]').forEach((range) => {
            const output = wizard.querySelector(`output[for="${CSS.escape(range.id)}"]`);
            if (!output) return;
            output.textContent = range.value;
            range.addEventListener('input', () => { output.textContent = range.value; });
        });
    };

    const initWizard = (wizard) => {
        let currentStep = Number.parseInt(wizard.dataset.currentStep ?? '1', 10);

        updateWizard(wizard, currentStep);
        initScaleOutputs(wizard);

        wizard.querySelectorAll('[data-survey-prev]').forEach((button) => {
            button.addEventListener('click', () => {
                currentStep -= 1;
                updateWizard(wizard, currentStep);
                currentStep = Number.parseInt(wizard.dataset.currentStep ?? '1', 10);
            });
        });

        wizard.querySelectorAll('[data-survey-next]').forEach((button) => {
            button.addEventListener('click', () => {
                const steps = Array.from(wizard.querySelectorAll('[data-survey-step]'));
                const activeStep = steps[currentStep - 1];
                if (activeStep && !validateStep(activeStep)) return;

                currentStep += 1;
                updateWizard(wizard, currentStep);
                currentStep = Number.parseInt(wizard.dataset.currentStep ?? '1', 10);
            });
        });

        const form = wizard.querySelector('form');
        if (form) {
            form.addEventListener('submit', (event) => {
                const steps = Array.from(wizard.querySelectorAll('[data-survey-step]'));
                const allValid = steps.every((step) => validateStep(step));
                if (!allValid) {
                    event.preventDefault();
                    const firstInvalidStep = steps.findIndex((step) => !validateStep(step));
                    if (firstInvalidStep !== -1) {
                        currentStep = firstInvalidStep + 1;
                        updateWizard(wizard, currentStep);
                        currentStep = Number.parseInt(wizard.dataset.currentStep ?? '1', 10);
                    }
                }
            });
        }
    };

    document.querySelectorAll('[data-survey-wizard]').forEach(initWizard);
})();
