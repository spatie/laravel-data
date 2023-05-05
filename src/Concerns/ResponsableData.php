<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Resolvers\PartialsTreeFromRequestResolver;
use Spatie\LaravelData\Support\Transformation\PartialTransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

trait ResponsableData
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $context = TransformationContextFactory::create()
            ->wrapExecutionType(WrapExecutionType::Enabled)
            ->get($this)
            ->mergePartials(
                PartialTransformationContext::create(
                    $this,
                    $this->getDataContext()->partialsDefinition
                )
            );

        $context = $this instanceof IncludeableDataContract
            ? $context->mergePartials(resolve(PartialsTreeFromRequestResolver::class)->execute($this, $request))
            : $context;

        return new JsonResponse(
            data: $this->transform($context),
            status: $request->isMethod(Request::METHOD_POST) ? Response::HTTP_CREATED : Response::HTTP_OK,
        );
    }

    public static function allowedRequestIncludes(): ?array
    {
        return [];
    }

    public static function allowedRequestExcludes(): ?array
    {
        return [];
    }

    public static function allowedRequestOnly(): ?array
    {
        return [];
    }

    public static function allowedRequestExcept(): ?array
    {
        return [];
    }
}
