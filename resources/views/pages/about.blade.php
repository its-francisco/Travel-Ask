@extends('layouts.app')

@section('title', 'About Us')

@section('content')

<section class="about-us container">

  <header>
    <img src="/images/travelling-about-travelask.png" alt="Decorative image showing girl travelling">
    <div>
      <h1>About Us</h1>
      <p>Welcome to Travel&Ask! Our website is dedicated to helping travelers find answers to their questions and share their experiences. Whether you're planning a trip or looking for advice, our community is here to help.</p>
    </div>
  </header>
    <div class="destaque">
      <h2>Our Mission</h2>
      <p>Our mission is to create a platform where travelers can connect, share knowledge, and help each other. We believe that by sharing our experiences, we can make travel easier and more enjoyable for everyone.</p>
    </div>
    <h2>The Team</h2>
    <div class="team">
        <div class="team-member">
            <figure>
              <img src="/images/team/FilipeCorreia.jpg" alt="Filipe Correia">
              <figcaption>Filipe Correia</figcaption>
            </figure>
            <p>Developer</p>
        </div>
        <div class="team-member">
            <figure>
              <img src="/images/team/FilipeGaio.jpg" alt="Filipe Gaio">
              <figcaption>Filipe Gaio</figcaption>
            </figure>
            <p>Developer</p>
        </div>
        <div class="team-member">
            <figure>
              <img src="/images/team/FranciscoFernandes.jpg" alt="Francisco Fernandes">
              <figcaption>Francisco Fernandes</figcaption>
            </figure>
            <p>Developer</p>
        </div>
        <div class="team-member">
            <figure>
              <img src="/images/team/LaraCoelho.jpg" alt="Lara Coelho">
              <figcaption>Lara Coelho</figcaption>
            </figure>
            <p>Developer</p>
        </div>
    </div>
</section>
<address class="contact container">
    <h2>Contact Us</h2>
    <p>If you have any questions or need assistance, feel free to reach out to us:</p>
    <ul>
        <li>Email: support@travelandask.com</li>
        <li>Phone: +1 (123) 456-7890</li>
        <li>Address: s/n Somewhere over the rainbow, Porto, Portugal</li>
    </ul>
</address>

<script type="application/ld+json">
    // So we have a schema
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Travel&Ask",
  "url": "https://www.travelandask.com",
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+1-123-456-7890",
    "contactType": "Customer Service",
    "email": "support@travelandask.com",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "sn Somewhere over the rainbow",
      "addressLocality": "Porto",
      "addressRegion": "Portugal"
    }
  },
  "member": [
    {
      "@type": "Person",
      "name": "Filipe Correia",
      "jobTitle": "Developer",
      "image": "https://www.travelandask.com/images/team/FilipeCorreia.jpg"
    },
    {
      "@type": "Person",
      "name": "Filipe Gaio",
      "jobTitle": "Developer",
      "image": "https://www.travelandask.com/images/team/FilipeGaio.jpg"
    },
    {
      "@type": "Person",
      "name": "Francisco Fernandes",
      "jobTitle": "Developer",
      "image": "https://www.travelandask.com/images/team/FranciscoFernandes.jpg"
    },
    {
      "@type": "Person",
      "name": "Lara Coelho",
      "jobTitle": "Developer",
      "image": "https://www.travelandask.com/images/team/LaraCoelho.jpg"
    }
  ]
}
</script>


@endsection