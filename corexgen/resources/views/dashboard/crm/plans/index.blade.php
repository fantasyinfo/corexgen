
@push('style')
    <style>
        .plan-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            overflow: hidden;
        }

        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .plan-card-header {
            background: linear-gradient(135deg, var(--primary-color), #6366f1);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .plan-card-body {
            padding: 30px;
        }

        .plan-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .plan-features {
            border-top: 1px solid rgba(0, 0, 0, 0.07);
            padding-top: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            color: var(--secondary-color);
        }

        .feature-icon {
            color: var(--accent-color);
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .btn-plan-action {
            width: 100%;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
    </style>
@endpush
@extends('layout.app')
@section('content')
@php
    prePrintR($plans);
@endphp
<div class="card shadow-sm rounded p-3 mb-3">
    <div class="card-header  border-bottom pb-2">
        <div class="row ">
            <div class="col-md-4">
                <h5 class="card-title">{{ __('plans.Plans Management') }}</h5>
            </div>
            @include('layout.components.header-buttons')
        </div>


    </div>
</div>


    <div class="row justify-content-center">

        @if(isset($plans) && $plans->isNotEmpty())
            @foreach($plans as $plan)
            <div class="col-md-4">
                <div class="card plan-card">
                    <div class="plan-card-header">
                        <h3 class="mb-0">{{$plan->name}} Plan</h3>
                        <p>{{$plan->desc}} </p>
                    </div>
                    <div class="plan-card-body">
                        <div class="plan-price text-center">
                            $ {{$plan->price}}  <span class="text-muted" style="font-size: 1rem;">/{{$plan->billing_cycle}} </span>
                        </div>
                        <div class="plan-features">
                            @if($plan->users_limit === -1)
                            <div class="feature-item">
                                <span class="feature-icon">✓</span>
                                Unlimited Users Limit
                            </div>
                            @elseif($plan->users_limit > 0)
                            @endif
                         
                            <div class="feature-item text-muted">
                                <span class="feature-icon">✗</span>
                                Advanced Reporting
                            </div>
                        </div>
                        <div class="mt-4">
                            <button class="btn btn-outline-primary btn-plan-action">
                                Select Plan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
 
       
    </div>
@endsection
