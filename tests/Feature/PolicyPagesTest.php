<?php

declare(strict_types=1);

test('it renders the operational privacy and data governance policy page with 200 ok', function () {
    $response = $this->get(route('policy.privacy'));

    $response->assertStatus(200);
    $response->assertSee('Operational Data & Privacy Policy');
    $response->assertSee('Npontu Technologies Limited');
    $response->assertSee('Ghana Data Protection Act, 2012 (Act 843)');
    $response->assertSee('dpo@npontu.com');
    $response->assertSee('7 Years (Statutory)');
});

test('it renders the SRE terms of service and acceptable use policy page with 200 ok', function () {
    $response = $this->get(route('policy.terms'));

    $response->assertStatus(200);
    $response->assertSee('SRE Operations Acceptable Use & Terms of Service');
    $response->assertSee('Two-Way Operational Custody Non-Repudiation');
    $response->assertSee('Mandatory Outgoing Sign-Off');
    $response->assertSee('Mandatory Incoming Sign-On');
    $response->assertSee('Role-Based Access Control');
});

test('it renders the information security and SIEM audit standard page with 200 ok', function () {
    $response = $this->get(route('policy.security'));

    $response->assertStatus(200);
    $response->assertSee('Information Security & Cryptographic Audit Standard');
    $response->assertSee('Zero-Trust Infrastructure');
    $response->assertSee('AES-256');
    $response->assertSee('Polymorphic Forensic Audit Engine');
    $response->assertSee('120-Minute Inactivity Expire');
});

test('it renders the 99.98% SLA uptime commitment and escalation matrix page with 200 ok', function () {
    $response = $this->get(route('policy.sla'));

    $response->assertStatus(200);
    $response->assertSee('Service Level Agreement (SLA 99.98%) &amp; Incident Escalation', false);
    $response->assertSee('99.98% Operational Availability Commitment');
    $response->assertSee('P1 - Critical');
    $response->assertSee('Emergency Escalation Hierarchy Tree');
});

test('it renders the comprehensive multi-column footer with policy and corporate links on the public landing page', function () {
    $response = $this->get(route('landing'));

    $response->assertStatus(200);
    $response->assertSee(route('policy.privacy'));
    $response->assertSee(route('policy.terms'));
    $response->assertSee(route('policy.security'));
    $response->assertSee(route('policy.sla'));
    $response->assertSee('Corporate Headquarters');
    $response->assertSee('Accra-Cluster-01 (Primary SRE NOC)');
    $response->assertSee('Greater Accra Region, Ghana');
    $response->assertSee('ops@npontu.com');
    $response->assertSee('55 Feature Tests Passed');
});
