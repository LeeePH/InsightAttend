@if (session()->has('flash_message'))
    

<script>
    swal({
title: "{{session('flash_message.title')}}",
text: "{{session('flash_message.message')}}",
icon: "{{session('flash_message.level')}}",
button: true,
timer: 2500,
});
  </script>

@endif

@if (session()->has('flash_message_overlay'))
    

<script>
    swal({
title: "{{session('flash_message_overlay.title')}}",
text: "{{session('flash_message_overlay.message')}}",
icon: "{{session('flash_message_overlay.level')}}",
button: 'Okay',
});
  </script>

@endif

@if(session()->has('success'))
<script>
    swal({
        title: "Success",
        text: @json(session('success')),
        icon: "success",
        button: true,
    });
</script>
@endif

@if(session()->has('error'))
<script>
    swal({
        title: "Error",
        text: @json(session('error')),
        icon: "error",
        button: true,
    });
</script>
@endif

@if(session()->has('warning'))
<script>
    swal({
        title: "Warning",
        text: @json(session('warning')),
        icon: "warning",
        button: true,
    });
</script>
@endif

@if ($errors->any())
<script>
    swal({
        title: "Validation Error",
        text: @json($errors->first()),
        icon: "error",
        button: true,
    });
</script>
@endif
