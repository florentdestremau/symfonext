<?php

namespace App\Service;

use Symfony\Component\Form\Util\FormUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\Util\LiveFormUtility;

trait SubmitWithRequestFormTrait
{
    use ComponentWithFormTrait {
        submitForm as defaultSubmitForm;
    }

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    private function submitForm(bool $validateAll = true, ?Request $request = null): void
    {
        if (!$request) {
            $request = $this->requestStack->getCurrentRequest();
        }

        if (null !== $this->formView) {
            // Two scenarios can cause this:
            // 1) Not intended: form was already submitted and validated in the same main request.
            // 2) Expected: form was submitted during a sub-request (e.g., a batch action).
            //
            // Before 2.23, both cases triggered an exception.
            // Since 2.23, we reset the form (preserving its values) to handle case 2 correctly.
            $this->resetForm(true);
        }

        $form = $this->getForm();

        $name = $form->getName();
        $params = $this->formValues;
        $files = [];
        if ('' === $name) {
            $files = $request?->files->all();
        } elseif ($request?->request->has($name) || $request?->files->has($name)) {
            $default = $form->getConfig()->getCompound() ? [] : null;
            $files = $request?->files->get($name, $default);
        }


        if (\is_array($params) && \is_array($files)) {
            $data = FormUtil::mergeParamsAndFiles($params, $files);
        } else {
            $data = $params ?: $files;
        }


        $form->submit($data);
        $this->shouldAutoSubmitForm = false;

        if ($validateAll) {
            // mark the entire component as validated
            $this->isValidated = true;
            // set fields back to empty, as now the *entire* object is validated.
            $this->validatedFields = [];
        } else {
            // we only want to validate fields in validatedFields
            // but really, everything is validated at this point, which
            // means we need to clear validation on non-matching fields
            $this->clearErrorsForNonValidatedFields($form, $form->getName());
        }

        // re-extract the "view" values in case the submitted data
        // changed the underlying data or structure of the form
        $this->formValues = $this->extractFormValues($this->getFormView());

        // remove any validatedFields that do not exist in data anymore
        $this->validatedFields = LiveFormUtility::removePathsNotInData(
            $this->validatedFields ?? [],
            [$form->getName() => $this->formValues],
        );

        if (!$form->isValid()) {
            throw new UnprocessableEntityHttpException('Form validation failed in component.');
        }
    }
}
